<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\User\Entities\User;

#[Layout('front::layouts.app')]
class DoctorProfileLivewire extends Component
{

    public User $doc ;
    public function mount() {
      $doctor_id =   request()->route('doctor_id') ;

      $this->doc =  User::find($doctor_id);
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
