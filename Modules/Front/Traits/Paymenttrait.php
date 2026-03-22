<?php

namespace Modules\Front\Traits;

use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Transaction\app\Models\Transaction;
use Modules\AppointmentUser\app\Models\AppointmentUser;

trait Paymenttrait
{
    public function createZarinPlaPayment($initial_data)
    {

        $amount = $initial_data['amount'] . '0';
        $transaction =  $this->createTransaction($initial_data);
        $data = [
            // 'merchant_id' => setting(SettingKeyEnum::PAYMENT_ZARINPAL_STATUS),
            'amount' => intval($amount),
            'callback_url' => route('front.setAppointment.detail.zarinpal',['tracking_code' => $initial_data['tracking_code'] ]) . '?transaction_id=' . $transaction->id,
            'currency' => 'IRR',
            'description' => setting(SettingKeyEnum::SITE_TITLE) ?? "پرداخت هزینه نوبت",
            'metadata' => ['mobile' => $initial_data['mobile']],
        ];

        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
        ]);

        try {
            $result = curl_exec($ch);
            $err = curl_error($ch);
            $result = json_decode($result, true, JSON_PRETTY_PRINT);
            curl_close($ch);
            if ($err) {
                $this->dispatch('paymentErr', errmsg: $err);
            } else {
                if (isset($result['data']['code']) && $result['data']['code'] === 100) {
                    $au = $result['data']['authority'] ?? '';
                    $transaction->update(['au' => $au]);
                    redirect("https://www.zarinpal.com/pg/StartPay/$au")->send();
                    return;
                } else {
                    return $this->dispatch('paymentErr',  errmsg: $result);
                }
            }
            return;
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $this->dispatch('paymentErr',  errmsg: 'مشکل در ارسال به بانک. لطفا صفحه را رفرش و مجدد تلاش کنید');
        }
    }

    public function createPaystarPayment($data)
    {
        //
    }


}
