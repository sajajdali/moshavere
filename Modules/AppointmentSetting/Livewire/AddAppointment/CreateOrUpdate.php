<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment;

use Livewire\Component;

class CreateOrUpdate extends Component
{

    public function mount() {
       $sectionId =  request()->get('sectionId');
       $doctorId =  request()->get('doctorId');
    }
    public function render()
    {
        return view('appointmentsetting::livewire.add-appointment.create-or-update');
    }
}
