<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Livewire\Component;
use Hekmatinasser\Verta\Facades\Verta;

class ListOfAvailableDay extends Component
{
    public $specificDayDate;
    public function mount()
    {
        $sectionId =  request()->get('sectionId');
        $doctorId =  request()->get('doctorId');
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
