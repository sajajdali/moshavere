<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment;

use Livewire\Component;
use Hekmatinasser\Verta\Facades\Verta;

class ListOfAvailableAppointment extends Component
{
    public $specificDayDate;
    public function mount()
    {
        $sectionId =  request()->get('sectionId');
        $doctorId =  request()->get('doctorId');
    }

    public function GotoSpecificDay()
    {
        $date = Verta::parse( $this->specificDayDate)->toCarbon()->timestamp;
        return redirect()->route('admin.appointment.add.specificday', ['date' => $date]);
    }

    public function addAppointment($date)
    {
    }
    public function render()
    {
        return view('appointmentsetting::livewire.add-appointment.list-of-available-appointment');
    }
}
