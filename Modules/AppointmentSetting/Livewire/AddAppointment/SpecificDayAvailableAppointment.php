<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment;

use Livewire\Component;

class SpecificDayAvailableAppointment extends Component
{
    public $currentDate ;

    public function updated($property){
        match($property) {
            $property == 'currentDate' => $this->loadDifferentDayDetail(),
            default => '',
        } ;
    }

    public function loadDifferentDayDetail() {
        // if date has been change , this functio would be call

    }
    public function previousDay() {
        //go to previous day
    }
    public function nextDay() {
        //go to next day
    }
    public function render()
    {
        return view('appointmentsetting::livewire.add-appointment.specific-day-available-appointment');
    }
}
