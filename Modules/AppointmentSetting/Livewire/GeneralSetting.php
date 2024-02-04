<?php

namespace Modules\AppointmentSetting\Livewire;

use Livewire\Component;
use Modules\User\Entities\User;


class GeneralSetting extends Component
{
    public $doctor ;

    //day property
    public array $timeFrame = [] ;
    public array $counter = [
        'saturday' => 1 ,
        'sunday'   => 1 ,
        'monday'   => 1 ,
        'tuesday'  => 1 ,
        'wednesday'=> 1 ,
        'thursday'  => 1 ,
        'friday'   => 1 ,
    ] ;

    //visit property
    public $visitTime ;

    //maximum day 
    public $maxDayAvaialbe ;

    public function addCounter($day) {
        $this->counter[$day] = $this->counter[$day] + 1 ;
        $this->render() ;
    }
    public function removeCounter($day) {
        $this->counter[$day] = $this->counter[$day] - 1 ;
        $this->render() ;
    }

    public function addDayForDoctor() {
    //    submited final detailes here
    }
    public function mount() {
        $doctorId = request()->route('user');
        if (! empty($doctorId)) {
            $this->doctor = User::find($doctorId);

        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error','پزشک مورد نظر یافت نشد') ;
        }
    }

    public function render()
    {

        return view('appointmentsetting::livewire.general-setting');
    }
}
