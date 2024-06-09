<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\User\Entities\User;
use Modules\Front\app\Models\Comment;

#[Layout('front::layouts.app')]
class DoctorProfileLivewire extends Component
{

    public User $doc ;
    public array $fetchData = [];
    public function mount() {
      $doctor_id =   request()->route('doctor_id') ;
      $this->doc =  User::find($doctor_id);
      $this->fetchData['comments'] = Comment::doctroComments($this->doc->id);
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
