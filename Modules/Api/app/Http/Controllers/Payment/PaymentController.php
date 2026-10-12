<?php

namespace Modules\Api\App\Http\Controllers\Payment;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Front\Traits\PaymentGatewayTrait;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentDocAndOperatorNotification;

class PaymentController extends Controller
{
    use ApiHandlerTrait;
    use PaymentGatewayTrait;
    public function createPaymentLink(AppointmentUser $appointmentUser)
    {
        if ($appointmentUser->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL) {
            return redirect()->to(route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'پرداخت شما با موفقیت انجام شده است']));
        }
        $amount = $appointmentUser->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
        $user = $appointmentUser->user;
        $t_data = [
            'amount' => $amount,
            'user_id' => $user->id,
            'mobile' =>  $user->mobile,
            'appointmentUser_id' =>  $appointmentUser->id,
            'tracking_code' =>  $appointmentUser->tracking_code,
        ];
        if (checkIp()) {
            $amount = 1500;
        }
        $activeGateway = $this->activePaymentGateway();
        // set the callback URL dynamically
        $callbackUrl = route('api.appointment.payment.callback', ['appointmentUser' => $appointmentUser->id]);
        $description = 'کاربر پرداخت کننده : ' . $appointmentUser->user?->full_name ?? 'بدون نام' . 'شماره تماس: ' . $appointmentUser->user?->mobile ?? 'بدون موبایل' . 'شماره ردیف: ' . $appointmentUser->id;
        try {
            $payment = $this->purchaseFromGateway($activeGateway, (int) $amount, $description, $callbackUrl, $user->mobile);
        } catch (Exception $e) {
            Log::error('Error in payment purchase process', [
                'gateway' => $activeGateway,
                'error_message' => $e->getMessage(),
            ]);
            $payment = null;
        }
        if (! $payment) {
            return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'خطا در اتصال به درگاه پرداخت']);
        }

        $t_data['detail']['transactionId'] = $payment['transactionId'];
        $t_data['detail']['callback'] = $callbackUrl;
        $t_data['detail']['driver'] = $activeGateway;
        $this->createTransaction($t_data);
        return redirect()->to($payment['url']);
    }
    private function createTransaction($initial_data)
    {
        $transactionData = [
            'user_id' => $initial_data['user_id'],
            'transaction_code' =>  Transaction::generateTransactionCode(),
            'status' => TransactionStatusEnum::PENDING,
            'cost' => $initial_data['amount'],
            'total_cost' => $initial_data['amount'],
            'paid_by' => TransactionPaidEnum::ONLINE,
            'detail' => $initial_data['detail'],
        ];
        if (isset($initial_data['discount'])) {
            $transactionData['discount_id'] = $initial_data['discount']['discount_id'];
            $transactionData['cost'] =  $initial_data['amount'] . 0;
            $transactionData['discount_amount'] =  $initial_data['discount']['discount_amount'];
            $transactionData['discount_code'] =  $initial_data['discount']['discount_code'];
        }
        $appUser = AppointmentUser::find($initial_data['appointmentUser_id']);
        if ($appUser->transaction) {
            $t = $appUser->transaction->update($transactionData);
        } else {
            $t = $appUser->transaction()->create($transactionData);
        }
        return $t;
    }
    public function callback(AppointmentUser $appointmentUser, Request $request)
    {
        if ($appointmentUser->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT && $appointmentUser->transaction) {
            $activeGateway = $this->transactionPaymentGateway($appointmentUser->transaction->detail);
            try {
                $amount = $appointmentUser->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
                if (checkIp()) {
                    $amount = 1500;
                }
                $receipt = $this->verifyGatewayPayment(
                    $activeGateway,
                    (int) $amount,
                    $appointmentUser->transaction->detail['transactionId']
                );
                $appointmentUser->update([
                    'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                    'deadline_at' => null,
                ]);
                $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT);
                if (isset($smsTemplate)) {
                    $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
                }
                $newTdetail = array_merge($appointmentUser->transaction->detail ?? [], $this->gatewayReceiptDetail($activeGateway, $receipt));
                $appointmentUser->transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL, 'detail' => $newTdetail]);
                // if appointment is online
                if ($appointmentUser->kind == AppointmentUserKindEnum::ONLINE) {
                    $appointmentUser->online->first()->update(['status' => \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::ACCEPTED]);
                    // send online first message
                    if (setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS)) {
                        $appointmentUser->online->last()->messages()->create([
                            'user_id' => $appointmentUser->online->last()->user_id,
                            'answer_by' => 1,
                            'type' => AppointmentOnlineMessageTypeEnum::ANSWER,
                            'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
                            'body' => setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE) ?? 'سلام لطفا سوال خود را مطرح کنید',
                        ]);
                    }
                }
                if (isset($appointmentUser->doctor)) {
                    if( isset($appointmentUser->doctor->drStoreAppSms) && $appointmentUser->doctor->drStoreAppSms != true ) {
                        $smsToDoctor = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR);
                    }elseif(! isset($appointmentUser->doctor->drStoreAppSms)){
                        $smsToDoctor = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR);
                    }
                    if (isset($smsToDoctor)) {
                        $appointmentUser->notify(new AppointmentDocAndOperatorNotification($smsToDoctor, $appointmentUser->doctor->mobile));
                    }
                }
                return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'پرداخت با موفقیت انجام شد']);
            } catch (InvalidPaymentException $exception) {
                $appointmentUser->transaction->update(['status' => TransactionStatusEnum::REJECTED]);
                return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'خطا در انجام تراکنش']);
            } catch (Exception $e) {
                Log::error('Error in payment verification process', [
                    'gateway' => $activeGateway,
                    'error_message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);
                return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'خطا در انجام تراکنش']);
            }
        }

        return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code]);
    }
}
