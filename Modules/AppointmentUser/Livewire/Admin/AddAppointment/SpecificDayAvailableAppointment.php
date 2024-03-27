<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class SpecificDayAvailableAppointment extends Component
{
    //this propery shouldNOT exist in the final product
    public $tempMessage = null;

    public function updated($property)
    {
        match ($property) {
            $property == 'currentDate' => $this->loadDifferentDayDetail(),
            default => '',
        };
        $this->tempMessage = null;
    }

    public function loadDifferentDayDetail()
    {
        // if date has been change , this functio would be call
        $this->tempMessage = null;
    }
    public function previousDay()
    {
        sleep(1);
        //go to previous day
        $this->tempMessage = null;
    }
    public function nextDay()
    {
        //go to next day
        $this->tempMessage = null;
    }

    #[On('closeModal')]
    public function addLoading()
    {
        //this function is just for appearing loading and should be deleted
        sleep(2);
        $this->tempMessage = 'تغییرات با موفقیت اعمال شد';
    }

    public function mount()
    {
        $app = AppointmentSetting::find(request()->route('appId'));
        $date = AppointmentSetting::find(request()->route('date'));
        $listOfAppointment = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            return app('AppointmentUserService')->listAppointments($app);
        });
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.specific-day-available-appointment');
    }
}
