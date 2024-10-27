<?php

namespace Modules\Front\Livewire\SetAppointment;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\Title;
use Modules\AppointmentUser\app\Jobs\GenerateAppointmentCache;
use Shetabit\Multipay\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Place\app\Models\Place;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Support\Facades\Cache;
use Modules\Front\Traits\Paymenttrait;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Discount\app\Models\Discount;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\App\Notifications\AppointmentDocAndOperatorNotification;

#[Layout('front::layouts.app')]
#[Title('جزئیات نوبت')]
class AppointmentDetail extends Component
{
    use Paymenttrait;
    #[Locked]
    private $transactionId;
    #[Locked]
    public array $fetchData = [
        'stauts' => [
            'name' => '',
            'color' => '',
            'payment' => '',
            'price' => '',
        ],
        'cancel' => false,
        'description' => false,
        'socailmedia' => ['status' => false],
    ];

    public array $form = [];

    public function userCanCancell()
    {
        $setting = $this->fetchData['app']->setting;
        $can_be_Canceld = (isset($setting->cancellation_by_user)) &&  $setting->cancellation_by_user == true && ($this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL);
        if ($can_be_Canceld) {
            if ($this->fetchData['app']->date_visit->subDays($setting->cancellation_by_user)->gt(\now())) {
                $this->fetchData['cancel'] = true;
            }
        }
    }

    public function hasDescripion()
    {
        $status =  setting(SettingKeyEnum::APPOINTMENT_DESCRIPTION_STATUS);
        if (isset($status) && $status != false && !empty(setting(SettingKeyEnum::APPOINTMENT_DESCRIPTION))) {
            $this->fetchData['description'] = setting(SettingKeyEnum::APPOINTMENT_DESCRIPTION);
        }
    }

    public function placeSocialMedia()
    {
        $this->fetchData['socailmedia']['telegram']     =  $this->fetchData['place']->detail[Place::DETAIL_TELEGRAM_ADDRESS]  ?? false;
        $this->fetchData['socailmedia']['instagram']    =  $this->fetchData['place']->detail[Place::DETAIL_INSTAGRAM_ADDRESS] ?? false;
        $this->fetchData['socailmedia']['whatsapp']     =  $this->fetchData['place']->detail[Place::DETAIL_WHATSAPP_ADDRESS]  ?? false;
        if (
            $this->fetchData['socailmedia']['telegram']  ||
            $this->fetchData['socailmedia']['instagram'] ||
            $this->fetchData['socailmedia']['whatsapp']
        ) {
            $this->fetchData['socailmedia']['status'] = true;
        }
    }
    public function cancelAppontment()
    {
        $user = auth()->user();
        if ($this->fetchData['app']->user->id == $user->id) {
            $this->fetchData['app']->update([
                'status' => AppointmentUserStatusEnum::STATUS_CANCEL,
            ]);

            // send sms
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
            if (isset($smsTemplate)) {
                $this->fetchData['app']->notify(new AppointmentSmsNotification($smsTemplate));
                session()->flash('success', 'نوبت شما با موفقیت کنسل شد');
            }
            GenerateAppointmentCache::dispatch($this->fetchData['app']->setting);
            return redirect()->route('front.setAppointment.detail', ['tracking_code' => $this->fetchData['app']->tracking_code]);
        } else {
            abort(401);
        }
    }

    public function appStatus()
    {
        $this->fetchData['stauts']['name']    = $this->fetchData['app']->status->getName();
        $this->fetchData['stauts']['color']   = $this->fetchData['app']->status->getBadgeColor();
        $this->fetchData['stauts']['enum']    = $this->fetchData['app']->status;
        $this->fetchData['stauts']['payment'] = $this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
        if ($this->fetchData['stauts']['payment'] && $this->fetchData['app']->setting()->exists()) {
            if ($this->fetchData['app']->kind == AppointmentUserKindEnum::IN_PERSION) {
                $this->fetchData['stauts']['price'] = $this->fetchData['app']->setting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::IN_PERSON][AppointmentSetting::PRICE];
            } elseif ($this->fetchData['app']->kind == AppointmentUserKindEnum::ONLINE) {
                $this->fetchData['stauts']['price'] = $this->fetchData['app']->setting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE][AppointmentSetting::PRICE];
            }
            if (setting(SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS)) {
                $this->fetchData['payment']['termAndCondition'] = setting(SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION);
            }
        } elseif ($this->fetchData['stauts']['payment']) {
            // appointmentSetting has been deleted
            $this->fetchData['stauts']['price'] = $this->fetchData['app']->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
            if (setting(SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS)) {
                $this->fetchData['payment']['termAndCondition'] = setting(SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_DESCRIPTION);
            }
        }
        $this->fetchData['monitoring'] = ($this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_MONITORING);
    }
    public function messages()
    {
        return [
            'form.discount_code.required' => 'لطفا کد تخفیف خود را وارد کنید ',
            'form.discount_code.max' => 'کد تخفیف وارد شده صحیح نیست ',
            'form.discount_code.string' => 'کد تخفیف وارد شده صحیح نیست ',
        ];
    }
    public function discount()
    {
        $this->validate(['form.discount_code' => 'required|string|max:225']);
        $discount = Discount::where('code', $this->form['discount_code'])
            ->where(function ($query) {
                return $query->where('service_id', $this->fetchData['app']->service->id)
                    ->orWhere('service_id', null);
            })
            ->where(function ($query) {
                return $query->where('doctor_id', $this->fetchData['app']->doctor->id)
                    ->orWhere('doctor_id', null);
            })
            ->where('active', ActiveEnum::ACTIVE)
            ->first();

        if (isset($discount) && !empty($discount)) {
            if ($discount->discountCanBeUsed($this->fetchData['app']->user->id,   $this->fetchData['stauts']['price'])) {
                return $this->addError('form.discount_code', $discount->discountIssue($this->fetchData['app']->user->id,   $this->fetchData['stauts']['price']));
            }
            //discount is useable
            $this->fetchData['discount_data'] = $discount;
            $finalPrice =  $discount->caculatePrice($this->fetchData['stauts']['price']);
            $this->fetchData['status']['price_after_discount'] = $finalPrice;
        } else {
            return  $this->addError('form.discount_code', 'کد تخفیف وارد شده اشتباه است!');
        }
    }
    public function authNeeded()
    {
        session()->put('url.intended', route('front.setAppointment.detail', ['tracking_code' => $this->fetchData['app']->tracking_code]));
        return redirect()->route('front.login.user', ['cancelApp' => true]);
    }
    public function GotoPayment()
    {
        $amount = $this->fetchData['stauts']['price'];
        $t_data = [
            'amount' => $this->fetchData['stauts']['price'],
            'user_id' => $this->fetchData['app']->user->id,
            'mobile' =>  $this->fetchData['app']->user->mobile,
            'appointmentUser_id' =>  $this->fetchData['app']->id,
            'tracking_code' =>  $this->fetchData['app']->tracking_code,
        ];
        if (isset($this->fetchData['discount_data'])) {
            $t_data['discount']['discount_id'] = $this->fetchData['discount_data']->id;
            $t_data['discount']['discount_amount'] = $this->fetchData['stauts']['price'] -  $t_data['amount'];
            $t_data['discount']['discount_code'] = $this->fetchData['discount_data']->code;
            $amount = $t_data['discount']['discount_amount'];
        }
        // set the callback URL dynamically
        $callbackUrl = route('front.setAppointment.detail', ['tracking_code' => $this->fetchData['app']->tracking_code, 'call_back' => true]);
        // Config::set('payment.zarinpal.callback_url', $callbackUrl);
        $description = 'کاربر پرداخت کننده : ' . $this->fetchData['app']->user?->full_name ?? 'بدون نام' . 'شماره تماس: ' . $this->fetchData['app']->user?->mobile ?? 'بدون موبایل' . 'شماره ردیف: ' . $this->fetchData['app']->id;
        $invoice = (new Invoice)->amount($amount)
            ->detail('description', $description)
            ->via(setting(SettingKeyEnum::PAYMEN_ACTIVE_DRIVER));
        $merchenId = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        $p =  Payment::config(['callbackUrl' => $callbackUrl, 'merchantId' => $merchenId])->purchase(
            $invoice,
            function ($driver, $transactionId) {
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
        // Check if a transaction exists
        if ($appUser->transaction) {
            $t = $appUser->transaction->update($transactionData);
        } else {
            $t = $appUser->transaction()->create($transactionData);
        }
        return $t;
    }
    public function bankCallback()
    {
        if (! $this->fetchData['app']->details[AppointmentUser::DETAIL_PAYMENT]['status']) {
            $this->fetchData['alert'] = 'خطا در انجام تراکنش';
            $this->fetchData['app']->transaction->update(['status' => TransactionStatusEnum::REJECTED]);
            return;
        }
        if ($this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT) {
            try {
                $amount = $this->fetchData['app']->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];
                $receipt = Payment::amount($amount)
                    ->transactionId($this->fetchData['app']->transaction->detail['transactionId'])->verify();
                $this->fetchData['app']->update([
                    'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                    'deadline_at' => null
                ]);
                $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT);
                if (isset($smsTemplate)) {
                    $this->fetchData['app']->notify(new AppointmentSmsNotification($smsTemplate));
                }
                // sms to operator and doctor
                if (isset($appointmentUser->operator)) {
                    $smsToOperator = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_OPERATOR);
                    if (isset($smsToOperator)) {
                        $this->fetchData['app']->notify(new AppointmentDocAndOperatorNotification($smsToOperator, $$this->fetchData['app']->operator->mobile));
                    }
                }
                if (isset($appointmentUser->doctor)) {
                    $smsToDoctor = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR);
                    if (isset($smsToDoctor)) {
                        $this->fetchData['app']->notify(new AppointmentDocAndOperatorNotification($smsToDoctor, $$this->fetchData['app']->doctor->mobile));
                    }
                }
                // if appointment is online
                if ($this->fetchData['app']->kind == AppointmentUserKindEnum::ONLINE) {
                    $this->fetchData['app']->online->first()->update(['status' => \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::ACCEPTED]);
                    // send online first message
                    if (setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS)) {
                        $this->fetchData['app']->online->last()->messages()->create([
                            'user_id' => $this->fetchData['app']->online->last()->user_id,
                            'answer_by' => 1,
                            'type' => AppointmentOnlineMessageTypeEnum::ANSWER,
                            'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
                            'body' => setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE) ?? 'سلام لطفا سوال خود را مطرح کنید',
                        ]);
                    }
                }

                $this->fetchData['success']  = 'پرداخت باموفقیت انجام شد و نوبت شما فعال شد ';
                $tDetail =  $this->fetchData['app']->transaction->detail;
                $respondDetaul = $receipt->getDetails();
                $newTdetail = array_merge($tDetail, [
                    'card_hash' => $respondDetaul['card_hash'],
                    'ref_id' => $respondDetaul['ref_id'],
                ]);
                $this->fetchData['app']->transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL, 'detail' => $newTdetail]);
                $this->render();
            } catch (InvalidPaymentException $exception) {
                $this->fetchData['alert'] = 'خطا در انجام تراکنش';
                $this->fetchData['app']->transaction->update(['status' => TransactionStatusEnum::REJECTED]);
            }
        }
    }
    public function mount()
    {

        $trackingCode  = request()->route('tracking_code');
        $cleanedTrackingCode = preg_replace('/[^0-9]/', '', $trackingCode);
        $this->fetchData['app'] = AppointmentUser::firstWhere('tracking_code', $cleanedTrackingCode);
        if (isset($this->fetchData['app'])) {
            if (request()->has('call_back')) {
                $this->bankCallback();
            }
            $this->fetchData['place'] = $this->fetchData['app']->place;
            $this->userCanCancell();
            $this->hasDescripion();
            $this->appStatus();
            $this->placeSocialMedia();
        } else {
            abort(404);
        }

        if (isset($this->fetchData['app']->place->detail[Place::DETAIL_KEY_LOCATION])) {
            $latitude = $this->fetchData['app']->place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LAT];
            $longitude = $this->fetchData['app']->place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LNG];
            $this->fetchData['mapUrl'] = "https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d642.0232600631508!2d{$longitude}!3d{$latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2s!4v1716538755171!5m2!1sen!2s";
            $this->fetchData['navigation'] = "https://maps.google.com/maps?daddr={$latitude},{$longitude}";
        }
        if (isset($this->fetchData['app']->details[AppointmentUser::STORE_FROM_APPLICATION]) && $this->fetchData['app']->details[AppointmentUser::STORE_FROM_APPLICATION] != false) {
            $urlToApplication = 'https://webapp.mata-app.com/transaction/show/' . $this->fetchData['app']->transaction?->id ?? '#';
            $this->fetchData['returnToApp'] = $urlToApplication;
        }
        $this->fetchData['authCheck'] = auth()->check();
        if (request()->has('msg')) {
            if (request()->get('msg') == 'پرداخت با موفقیت انجام شد') {
                $this->fetchData['success'] = request()->get('msg');
            } else {
                $this->fetchData['alert'] = request()->get('msg');
            }
        }
    }
    public function render()
    {
        return view('front::livewire.set-appointment.appointment-detail');
    }
}
