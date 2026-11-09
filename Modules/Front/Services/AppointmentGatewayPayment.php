<?php

namespace Modules\Front\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Notifications\AppointmentDocAndOperatorNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Front\Traits\PaymentGatewayTrait;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

/**
 * شروع و تکمیل پرداخت آنلاین نوبت با درگاه بانکی.
 * بین قالب جدید (PaymentController) و قالب قدیم (لایوایر AppointmentDetail) مشترک است.
 *
 * - هر نوبت در هر لحظه فقط یک درخواست پرداخت/تایید دارد (قفل کش)، پس کلیک های پشت سر هم
 *   فقط یک توکن از بانک میگیرند و توکن تازه دوباره استفاده میشود.
 * - همه توکن های صادر شده نگه داشته میشوند تا اگر کاربر با توکن قبلی پرداخت کرد، تایید
 *   با همان توکن و همان مبلغ انجام شود.
 * - پاسخ بانک (callback و تایید) کامل در جزئیات تراکنش ذخیره میشود.
 */
class AppointmentGatewayPayment
{
    use PaymentGatewayTrait;

    /** کلیک دوباره در این فاصله همان توکن قبلی را باز میکند */
    private const REUSE_TOKEN_SECONDS = 120;

    /** حداقل زمانی که نوبت هنگام رفتن به بانک نگه داشته میشود تا کرون مهلت پرداخت آن را حذف نکند */
    private const BANK_SESSION_MINUTES = 20;

    private const MAX_ISSUED_TOKENS = 10;

    public const RESULT_SUCCESS = 'success';
    public const RESULT_FAILED = 'failed';
    public const RESULT_ERROR = 'error';

    /**
     * ساخت (یا استفاده دوباره از) توکن پرداخت.
     *
     * @param  array{cost?: int, discount?: array{discount_id: int, discount_amount: int, discount_code: string}}  $transactionExtra
     * @return array{status: bool, url?: string, message?: string, already_paid?: bool}
     */
    public function start(AppointmentUser $appointment, int $amount, string $callbackUrl, array $transactionExtra = []): array
    {
        $lock = Cache::lock($this->lockKey($appointment->id), 40);

        try {
            // کلیک دوم منتظر اولی میماند و بعد همان توکن را میگیرد
            $lock->block(30);
        } catch (LockTimeoutException) {
            return ['status' => false, 'message' => 'درخواست پرداخت شما در حال پردازش است؛ لطفا چند لحظه صبر کنید.'];
        }

        try {
            $appointment = AppointmentUser::withTrashed()->with(['transaction', 'user'])->find($appointment->id);

            if ($problem = $this->cannotStartReason($appointment, $amount)) {
                return ['status' => false, 'message' => $problem, 'already_paid' => $appointment?->transaction?->status === TransactionStatusEnum::SUCCESSFUL];
            }

            $transaction = $appointment->transaction;
            $payableAmount = $this->payableGatewayAmount($amount);
            $detail = $transaction?->detail ?? [];

            if ($this->canReuseToken($transaction, $payableAmount, $callbackUrl)) {
                return ['status' => true, 'url' => $detail['payment_url']];
            }

            $gateway = $this->activePaymentGateway();
            $user = $appointment->user;

            try {
                $payment = $this->purchaseFromGateway(
                    $gateway,
                    $payableAmount,
                    $this->paymentDescription($appointment),
                    $callbackUrl,
                    $user?->mobile
                );
            } catch (\Throwable $e) {
                Log::error('Appointment payment purchase failed', [
                    'appointment_id' => $appointment->id,
                    'gateway' => $gateway,
                    'error_message' => $e->getMessage(),
                ]);
                $payment = null;
            }

            if (! $payment || empty($payment['transactionId'])) {
                return ['status' => false, 'message' => 'خطا در اتصال به درگاه پرداخت؛ لطفا دوباره تلاش کنید.'];
            }

            $now = now();
            // توکن های قبلی فقط وقتی معتبرند که تراکنش هنوز پرداخت نشده باشد
            $issued = $transaction && $transaction->status !== TransactionStatusEnum::SUCCESSFUL
                ? ($detail['issued_tokens'] ?? [])
                : [];
            $issued[] = [
                'token' => (string) $payment['transactionId'],
                'driver' => $gateway,
                'amount' => $payableAmount,
                'order_id' => $payment['orderId'] ?? null,
                'at' => $now->toDateTimeString(),
            ];

            $newDetail = array_merge($detail, [
                'driver' => $gateway,
                'transactionId' => (string) $payment['transactionId'],
                'order_id' => $payment['orderId'] ?? null,
                'callback' => $callbackUrl,
                'payment_url' => $payment['url'],
                'gateway_amount' => $payableAmount,
                'requested_at' => $now->toDateTimeString(),
                'issued_tokens' => array_slice($issued, -self::MAX_ISSUED_TOKENS),
            ]);
            // نتیجه تلاش قبلی نباید روی تلاش جدید نمایش داده شود
            unset($newDetail['verify_error']);

            $transactionData = [
                'user_id' => $appointment->user_id,
                'status' => TransactionStatusEnum::PENDING,
                'cost' => $transactionExtra['cost'] ?? $amount,
                'total_cost' => $amount,
                'paid_by' => TransactionPaidEnum::ONLINE,
                'detail' => $newDetail,
                // تخفیف تلاش قبلی نباید روی تلاش بدون تخفیف باقی بماند
                'discount_id' => $transactionExtra['discount']['discount_id'] ?? null,
                'discount_amount' => (int) ($transactionExtra['discount']['discount_amount'] ?? 0),
                'discount_code' => $transactionExtra['discount']['discount_code'] ?? null,
            ];

            if ($transaction) {
                $transaction->update($transactionData);
            } else {
                $appointment->transaction()->create($transactionData + [
                    'transaction_code' => Transaction::generateTransactionCode(),
                ]);
            }

            // تا وقتی کاربر در صفحه بانک است، نوبت نباید به خاطر پایان مهلت حذف شود
            $minDeadline = $now->copy()->addMinutes(self::BANK_SESSION_MINUTES);
            if ($appointment->deadline_at && Carbon::parse($appointment->deadline_at)->lt($minDeadline)) {
                $appointment->update(['deadline_at' => $minDeadline]);
            }

            return ['status' => true, 'url' => $payment['url']];
        } finally {
            $lock->release();
        }
    }

    /**
     * تایید پرداخت پس از بازگشت از بانک. تکرار callback (رفرش صفحه) نتیجه قبلی را برمیگرداند.
     *
     * @return array{status: string, message: string}
     */
    public function complete(int $appointmentId, Request $request): array
    {
        $lock = Cache::lock($this->lockKey($appointmentId), 60);

        try {
            $lock->block(45);
        } catch (LockTimeoutException) {
            return [
                'status' => self::RESULT_ERROR,
                'message' => 'پرداخت شما در حال بررسی است؛ چند لحظه دیگر صفحه را دوباره باز کنید.',
            ];
        }

        $activated = false;

        try {
            $appointment = AppointmentUser::withTrashed()->with('transaction')->find($appointmentId);
            $transaction = $appointment?->transaction;

            if (! $transaction) {
                return ['status' => self::RESULT_ERROR, 'message' => 'تراکنشی برای این نوبت پیدا نشد.'];
            }

            if ($transaction->status === TransactionStatusEnum::SUCCESSFUL) {
                return ['status' => self::RESULT_SUCCESS, 'message' => 'پرداخت این نوبت پیش تر با موفقیت ثبت شده است.'];
            }

            $detail = $transaction->detail ?? [];
            $detail['callback_response'] = $this->callbackPayload($request);
            $detail['callback_at'] = now()->toDateTimeString();

            $issued = $this->matchIssuedToken($detail, $request);
            if ($issued === false) {
                Log::warning('Payment callback token was not issued for this appointment', [
                    'appointment_id' => $appointmentId,
                    'callback' => $detail['callback_response'],
                ]);
                $transaction->update(['detail' => $detail]);

                return ['status' => self::RESULT_FAILED, 'message' => 'اطلاعات بازگشتی از بانک با این نوبت مطابقت ندارد.'];
            }

            $gateway = $issued['driver'] ?? $this->transactionPaymentGateway($detail);
            $amount = (int) ($issued['amount'] ?? $detail['gateway_amount'] ?? $this->payableGatewayAmount((int) $transaction->total_cost));

            try {
                $receipt = $this->verifyGatewayPayment($gateway, $amount, (string) $issued['token']);
            } catch (InvalidPaymentException $e) {
                $detail['verify_error'] = [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'at' => now()->toDateTimeString(),
                ];
                $transaction->update(['status' => TransactionStatusEnum::REJECTED, 'detail' => $detail]);

                return [
                    'status' => self::RESULT_FAILED,
                    'message' => 'پرداخت ناموفق بود: '.$e->getMessage(),
                ];
            } catch (\Throwable $e) {
                // نتیجه نامشخص است (مثلا قطعی ارتباط با بانک)؛ تراکنش در حال انجام میماند
                Log::error('Appointment payment verification failed', [
                    'appointment_id' => $appointmentId,
                    'gateway' => $gateway,
                    'error_message' => $e->getMessage(),
                ]);
                $detail['verify_error'] = [
                    'code' => null,
                    'message' => 'خطا در ارتباط با بانک هنگام تایید پرداخت',
                    'at' => now()->toDateTimeString(),
                ];
                $transaction->update(['detail' => $detail]);

                return [
                    'status' => self::RESULT_ERROR,
                    'message' => 'تایید پرداخت با خطا مواجه شد. اگر مبلغ از حساب شما کسر شده باشد، حداکثر تا ۷۲ ساعت آینده به حساب شما بازمیگردد.',
                ];
            }

            $detail = array_merge($detail, $this->gatewayReceiptDetail($gateway, $receipt), [
                'paid_token' => (string) $issued['token'],
                'paid_amount' => $amount,
                'paid_at' => now()->toDateTimeString(),
                'reference_id' => $receipt->getReferenceId(),
                'receipt' => $receipt->getDetails(),
            ]);
            unset($detail['verify_error']);

            $activated = DB::transaction(function () use ($appointmentId, $transaction, $detail) {
                $appointment = AppointmentUser::withTrashed()->whereKey($appointmentId)->lockForUpdate()->first();

                // پول دریافت شده ولی نوبت دیگر در انتظار پرداخت نیست (حذف یا لغو شده)
                $canActivate = $appointment
                    && ! $appointment->trashed()
                    && $appointment->status === AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;

                if (! $canActivate) {
                    $detail['needs_review'] = 'paid_but_appointment_not_waiting_for_payment';
                }

                $transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL, 'detail' => $detail]);

                if ($canActivate) {
                    $appointment->update([
                        'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                        'deadline_at' => null,
                    ]);
                }

                return $canActivate;
            });

            if (! $activated) {
                Log::warning('Payment verified but appointment could not be activated', [
                    'appointment_id' => $appointmentId,
                    'transaction_id' => $transaction->id,
                ]);

                return [
                    'status' => self::RESULT_SUCCESS,
                    'message' => 'پرداخت شما با موفقیت انجام شد اما این نوبت دیگر فعال نیست؛ لطفا برای پیگیری با پشتیبانی تماس بگیرید.',
                ];
            }
        } finally {
            $lock->release();
        }

        // اعلان ها بعد از ثبت قطعی؛ خطای پیامک نباید نتیجه پرداخت را خراب کند
        $this->afterActivated(AppointmentUser::with(['doctor', 'operator', 'online'])->find($appointmentId));

        return ['status' => self::RESULT_SUCCESS, 'message' => 'پرداخت با موفقیت انجام شد و نوبت شما فعال شد.'];
    }

    private function cannotStartReason(?AppointmentUser $appointment, int $amount): ?string
    {
        if (! $appointment || $appointment->trashed()) {
            return 'مهلت پرداخت این نوبت به پایان رسیده و نوبت حذف شده است.';
        }

        if ($appointment->transaction?->status === TransactionStatusEnum::SUCCESSFUL
            || $appointment->status === AppointmentUserStatusEnum::STATUS_SUCCESSFUL) {
            return 'پرداخت این نوبت پیش تر با موفقیت انجام شده است.';
        }

        if ($appointment->status !== AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT) {
            return 'این نوبت در انتظار پرداخت نیست.';
        }

        if ($appointment->deadline_at && Carbon::parse($appointment->deadline_at)->isPast()) {
            return 'مهلت پرداخت این نوبت به پایان رسیده است.';
        }

        if ($amount <= 0) {
            return 'مبلغ قابل پرداخت برای این نوبت مشخص نشده است.';
        }

        return null;
    }

    /**
     * توکنی که همین چند لحظه پیش گرفته شده و هنوز بانک به آن پاسخی نداده، دوباره استفاده میشود
     * (دوبار کلیک، رفرش یا برگشت مرورگر) تا درخواست تازه ای به بانک نرود.
     */
    private function canReuseToken(?Transaction $transaction, int $payableAmount, string $callbackUrl): bool
    {
        if (! $transaction || $transaction->status !== TransactionStatusEnum::PENDING) {
            return false;
        }

        $detail = $transaction->detail ?? [];
        if (empty($detail['payment_url']) || empty($detail['requested_at'])) {
            return false;
        }

        $requestedAt = Carbon::parse($detail['requested_at']);
        $answered = ! empty($detail['callback_at']) && Carbon::parse($detail['callback_at'])->gte($requestedAt);

        return ! $answered
            && $requestedAt->gt(now()->subSeconds(self::REUSE_TOKEN_SECONDS))
            && (int) ($detail['gateway_amount'] ?? -1) === $payableAmount
            && ($detail['callback'] ?? null) === $callbackUrl
            && ($detail['driver'] ?? null) === $this->activePaymentGateway();
    }

    /**
     * توکن برگشتی از بانک را با توکن های صادر شده تطبیق میدهد.
     *
     * @return array|false توکن صادر شده متناظر، یا false اگر توکن برگشتی متعلق به این نوبت نباشد
     */
    private function matchIssuedToken(array $detail, Request $request): array|false
    {
        $issued = $detail['issued_tokens'] ?? [];
        // تراکنش های قدیمی که پیش از این تغییر ساخته شده اند
        if ($issued === [] && ! empty($detail['transactionId'])) {
            $issued[] = [
                'token' => (string) $detail['transactionId'],
                'driver' => $detail['driver'] ?? null,
                'amount' => $detail['gateway_amount'] ?? null,
            ];
        }

        if ($issued === []) {
            return false;
        }

        // کلید هایی که درگاه ها توکن را با آن برمیگردانند
        $returned = null;
        foreach (['token', 'Token', 'Authority', 'trackId', 'RefId'] as $key) {
            $value = $request->input($key);
            if (is_scalar($value) && (string) $value !== '') {
                $returned = (string) $value;
                foreach ($issued as $item) {
                    if ((string) $item['token'] === $returned) {
                        return $item;
                    }
                }
            }
        }

        $last = end($issued);

        // سداد توکن را همیشه برمیگرداند؛ توکن ناشناس یعنی callback مربوط به این نوبت نیست
        if ($returned !== null && ($last['driver'] ?? $this->transactionPaymentGateway($detail)) === 'sadad') {
            return false;
        }

        return $last;
    }

    /**
     * داده های بازگشتی بانک برای ذخیره (فقط مقادیر ساده و با طول محدود).
     */
    private function callbackPayload(Request $request): array
    {
        $payload = [];
        foreach ($request->except(['_token']) as $key => $value) {
            if (count($payload) >= 40) {
                break;
            }
            if (is_scalar($value) || $value === null) {
                $payload[mb_substr((string) $key, 0, 64)] = $value === null ? null : mb_substr((string) $value, 0, 500);
            }
        }

        return $payload;
    }

    private function paymentDescription(AppointmentUser $appointment): string
    {
        return 'کاربر پرداخت کننده: '.($appointment->user?->full_name ?: 'بدون نام')
            .' - شماره تماس: '.($appointment->user?->mobile ?: 'بدون موبایل')
            .' - شماره ردیف: '.$appointment->id;
    }

    private function afterActivated(?AppointmentUser $appointment): void
    {
        if (! $appointment) {
            return;
        }

        $this->safely(function () use ($appointment) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT);
            if (! empty($smsTemplate)) {
                $appointment->notify(new AppointmentSmsNotification($smsTemplate));
            }
        });

        if ($appointment->kind === AppointmentUserKindEnum::ONLINE) {
            $this->safely(function () use ($appointment) {
                $appointment->online->first()?->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED]);

                $online = $appointment->online->last();
                if ($online && setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS)) {
                    $online->messages()->create([
                        'user_id' => $online->user_id,
                        'answer_by' => 1,
                        'type' => AppointmentOnlineMessageTypeEnum::ANSWER,
                        'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
                        'body' => setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE) ?? 'سلام لطفا سوال خود را مطرح کنید',
                    ]);
                }
            });
        }

        if ($appointment->operator?->mobile) {
            $this->safely(function () use ($appointment) {
                $smsToOperator = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_OPERATOR);
                if (! empty($smsToOperator)) {
                    $appointment->notify(new AppointmentDocAndOperatorNotification($smsToOperator, $appointment->operator->mobile));
                }
            });
        }

        $doctor = $appointment->doctor;
        // پزشکی که پیامک ثبت نوبت را خاموش کرده (drStoreAppSms = true) پیامک نمیگیرد
        if ($doctor && $doctor->drStoreAppSms != true) {
            $this->safely(function () use ($appointment, $doctor) {
                $smsToDoctor = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR);
                if (! empty($smsToDoctor)) {
                    $appointment->notify(new AppointmentDocAndOperatorNotification($smsToDoctor, $doctor->mobile));
                }
            });
        }
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function lockKey(int $appointmentId): string
    {
        return 'appointment-gateway-payment:'.$appointmentId;
    }
}
