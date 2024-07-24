<?php

namespace Modules\Api\app\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Shetabit\Multipay\Invoice;
use App\Http\Controllers\Controller;
use Shetabit\Payment\Facade\Payment;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Api\Transformers\UserResource;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;

class PaymentController extends Controller
{ 
    use ApiHandlerTrait;
    private $transactionId;
    public function createPaymentLink(AppointmentUser $appointmentUser)
    {
        $amount = $appointmentUser->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
        $user = $appointmentUser->user;
        $t_data = [
            'amount' => $amount,
            'user_id' => $user->id,
            'mobile' =>  $user->mobile,
            'appointmentUser_id' =>  $appointmentUser->id,
            'tracking_code' =>  $appointmentUser->tracking_code,
        ];
        // set the callback URL dynamically
        $callbackUrl = route('api.appointment.payment.callback', ['appointmentUser' => $appointmentUser->id]);
        config(['payment.zarinpal.callback_url' => $callbackUrl]);

        // Retrieve json format of Redirection (in this case you can handle redirection to bank gateway)
        $p =   Payment::purchase(
            ($invoce  = new Invoice)->amount($amount),
            function ($driver, $transactionId) use ($invoce) {
                $invoce->via(setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER));
                $this->transactionId = $transactionId;
            }
        )->pay()->toJson();
        $t_data['detail']['transactionId'] = $this->transactionId;
        $t_data['detail']['driver'] = setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER);
        $this->createTransaction($t_data);
        return redirect()->to(json_decode($p, true)['action']);
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
        $t =  $appUser->transaction()->updateOrCreate($transactionData);
        return $t;
    }
    public function callback(AppointmentUser $appointmentUser, Request $request)
    {
        try {
            $receipt = Payment::amount($appointmentUser->cost)
                ->transactionId($appointmentUser->transaction->detail['transactionId'])->verify();
            $appointmentUser->update([
                'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL
            ]);
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT);
            if (isset($smsTemplate)) {
                $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
            }
            $appointmentUser->transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL]);
            return $this->ok([
                'status' => true,
                'message' => 'پرداخت با موفقیت انجام شد',
                'user' => UserResource::make($appointmentUser->user),
            ]);
        } catch (InvalidPaymentException $exception) {
            $appointmentUser->transaction->update(['status' => TransactionStatusEnum::REJECTED]);
            return $this->requestException([
                'status' => false,
                'message' => 'خطا در انجام تراکنش',
                'user' => UserResource::make($appointmentUser->user),
            ]);
        }
    }
}
