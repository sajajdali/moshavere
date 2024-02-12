<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use Modules\User\Entities\User;

class SpecialSectionSetting extends Component
{

    public $doctor ;

    public function editGeneralSetting() {
        return redirect()->route('admin.appointment.setting',['user'=> $this->doctor->id,'edit' => 'true']);
    }
    public function mount(){
        $doctorId = request()->route('user');
        if (! empty($doctorId)) {
            $this->doctor = User::find($doctorId);

        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error','پزشک مورد نظر یافت نشد') ;
        }
    }
    public function render()
    {
        return view('appointmentsetting::livewire.general-setting.special-section-setting');
    }

}
