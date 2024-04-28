<?php

namespace Modules\Front\Livewire\Appointment;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\AppointmentUser\app\Models\AppointmentUser;

#[Layout('front::layouts.app')]
class AppointmentDetail extends Component
{
    #[Locked]
    public array $fetchData = [
        'stauts' => 'false',
        'loc' => [
            'lat' => '' ,
            'lng' => '' ,
        ]
    ];

    public function mount()
    {
        if(request()->has('appointment-id')){
            $this->fetchData['app'] = AppointmentUser::firstWhere('traking_code',request()->get('appointment-id'));
        }
        // TODO::select app from url 
        $this->fetchData['app'] = AppointmentUser::find(19);
        $this->fetchData['place'] = $this->fetchData['app']->place ;

    }
    public function render()
    {
        return view('front::livewire.appointment.appointment-detail');
    }
}
