<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Livewire\Component;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class ListOfAvailableDay extends Component
{
    public $specificDayDate;
    public function mount()
    {
        $serviceId =  request()->get('sectionId');
        $doctorId =  request()->get('doctorId');
        $placeId =  request()->get('placeId');
        $appointmentSetting = AppointmentSetting::where('service_id',$serviceId)->where('place_id',$placeId)->get();
        if($appointmentSetting->isEmpty()) {
            $appointmentSetting = AppointmentSetting::where('user_id',$doctorId)->get();
        }
        if($appointmentSetting->isEmpty()) {
            return redirect()->route('admin.appointment.doctor.list')->with('error','لطفا ابتدا تنظیمات حضور پزشک را ثبت کنید');
        }
        //        Cache::forget('appointmentList.1');
                $listUsers = Cache::rememberForever('appointmentList.'.$appointmentSetting->id, function () use ($appointmentSetting) {
                    return app('AppointmentUserService')->listAppointments($appointmentSetting->id);
                });
    }

    public function GotoSpecificDay()
    {
        $date = Verta::parse($this->specificDayDate)->toCarbon()->timestamp;
        return redirect()->route('admin.appointment.add.specificday', ['date' => $date]);
    }


    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.list-of-available-day');
    }
}
