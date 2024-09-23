<?php

namespace Modules\Api\App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Shetabit\Multipay\Invoice;
use App\Http\Controllers\Controller;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Support\Facades\Config;
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

class PaymentController extends Controller
{
    use ApiHandlerTrait;
    private $transactionId;
    public function createPaymentLink(AppointmentUser $appointmentUser)
    {
        if ($appointmentUser->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL){
            return redirect()->to(route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code , 'msg' => 'پرداخت شما با موفقیت انجام شده است']));
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
        // set the callback URL dynamically
        $callbackUrl = route('api.appointment.payment.callback', ['appointmentUser' => $appointmentUser->id]);
        // Config::set('payment.zarinpal.callback_url', $callbackUrl);
        $description = 'کاربر پرداخت کننده : ' . $appointmentUser->user?->full_name ?? 'بدون نام' . 'شماره تماس: ' . $appointmentUser->user?->mobile ?? 'بدون موبایل' . 'شماره ردیف: ' . $appointmentUser->id;
        $invoice = (new Invoice)->amount($amount)->detail('description', $description)->via(setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER));
        // Retrieve json format of Redirection (in this case you can handle redirection to bank gateway)
        // $merchenId = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        // $p = Payment::config(['callbackUrl' => $callbackUrl, 'mechandId' => $merchenId])->purchase(
        //     $invoice,
        //     function ($driver, $transactionId) {
        //         $this->transactionId = $transactionId;
        //     }
        // )->pay()->toJson();
        $p =   Payment::callbackUrl($callbackUrl)->purchase(
            $invoice,
            function ($driver, $transactionId) {
                $this->transactionId = $transactionId;
            }
        )->pay()->toJson();

        $t_data['detail']['transactionId'] = $this->transactionId;
        $t_data['detail']['callback'] = $callbackUrl;

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
        if ($appUser->transaction) {
            $t = $appUser->transaction->update($transactionData);
        } else {
            $t = $appUser->transaction()->create($transactionData);
        }
        return $t;
    }
    public function callback(AppointmentUser $appointmentUser, Request $request)
    {
        try {
            $amount = $appointmentUser->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
            $receipt = Payment::amount($amount)
                ->transactionId($appointmentUser->transaction->detail['transactionId'])
                ->verify();
            $appointmentUser->update([
                'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL
            ]);
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT);
            if (isset($smsTemplate)) {
                $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
            }
            $appointmentUser->transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL]);

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

            return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'پرداخت با موفقیت انجام شد']);
        } catch (InvalidPaymentException $exception) {
            $appointmentUser->transaction->update(['status' => TransactionStatusEnum::REJECTED]);
            session()->flash('error', 'خطا در انجام تراکنش');
            return redirect()->route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code, 'msg' => 'خطا در انجام تراکنش']);
        }
    }
}
