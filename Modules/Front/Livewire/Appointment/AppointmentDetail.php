<?php

namespace Modules\Front\Livewire\Appointment;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Place\app\Models\Place;
use Modules\Setting\Enum\SettingKeyEnum;
use Barryvdh\Reflection\DocBlock\Description;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

#[Layout('front::layouts.app')]
class AppointmentDetail extends Component
{
    #[Locked]
    public array $fetchData = [
        'stauts' => [
            'name' => '',
            'color' => '',
            'payment' => '',
            'price' => '',
        ],
        'loc' => [
            'lat' => '',
            'lng' => '',
        ],
        'cancel' => false,
        'description' => false,
        'socailmedia' => ['status' => false],
    ];
    public function userCanCancell()
    {
        $setting = $this->fetchData['app']->setting;
        $can_be_Canceld = ($setting->cancellation_by_user != null) && ($this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL);
        if ($can_be_Canceld) {
            if ($this->fetchData['app']->date_visit->addDays($setting->cancellation_by_user)->gt(\now())) {
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
    public function appStatus()
    {
        $this->fetchData['stauts']['name']    = $this->fetchData['app']->status->getName();
        $this->fetchData['stauts']['color']   = $this->fetchData['app']->status->getBadgeColor();
        $this->fetchData['stauts']['enum']    = $this->fetchData['app']->status;
        $this->fetchData['stauts']['payment'] = $this->fetchData['app']->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
        if ($this->fetchData['stauts']['payment']) {
            $this->fetchData['stauts']['price'] = $this->fetchData['app']->setting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::PRICE];
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
    #[On('confirm_swal')]
    public function cancelAppointment()
    {
        $app = $this->fetchData['app'];
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]);
        return redirect()->route('front.appointment.detail', ['tracking_code' => $this->fetchData['app']->tracking_code]);
    }
    public function mount()
    {

        $trackingCode  = request()->route('tracking_code');
        $cleanedTrackingCode = preg_replace('/[^0-9]/', '', $trackingCode);
        $this->fetchData['app'] = AppointmentUser::firstWhere('tracking_code', $cleanedTrackingCode);
        if (isset($this->fetchData['app'])) {
            $this->fetchData['place'] = $this->fetchData['app']->place;
            $this->userCanCancell();
            $this->hasDescripion();
            $this->appStatus();
            $this->placeSocialMedia();
        }
    }
    public function render()
    {
        return view('front::livewire.appointment.appointment-detail');
    }
}
