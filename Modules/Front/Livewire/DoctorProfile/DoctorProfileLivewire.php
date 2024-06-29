<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\Front\app\Models\Comment;

#[Layout('front::layouts.app')]
class DoctorProfileLivewire extends Component
{

    #[Locked]
    public User $doc;
    #[Locked]
    public array $fetchData = [];
    public array $form = [];

    public function reserveAppointment()
    {
        // lunch modal 
        if ($this->doc->places()->count() > 1) {
            $this->fetchData['places'] = $this->doc->places;
            $this->fetchData['modalStep'] = 1 ;
            return   $this->dispatch('lucnhModal', true);
        }
        if ($this->doc->services()->count() > 1) {
            $this->fetchData['services'] = $this->doc->services;
            return   $this->dispatch('lucnhModal', true);
        }

        // if less than one service exist , redirect to appointment days list 
      $this->redirectToAppointmentDays($this->doc->id,$this->doc->places()->first()->id,$this->doc->services()->first()->id);
    }
    private function redirectToAppointmentDays($doctor_id,$place_id,$service_id) {
        return redirect()->route(
            'front.setAppointment.days',
            [
                'doctor_id'     => $doctor_id,
                'place_id'      => $place_id,
                'service_id'    => $service_id
            ]
        );
    }
    public function modalSubmit() { 
        if($this->fetchData['modalStep'] == 1 ) { 

            if ($this->doc->services()->count() > 1) {
                $this->fetchData['services'] = $this->doc->services;
                return   $this->dispatch('lucnhModal', true);
            }

        }else{ 
            // service has been selected 
        }
    }
    public function mount()
    {
        $doctor_id =   request()->route('doctor_id');
        $this->doc =  User::find($doctor_id);
        $this->fetchData['comments'] = Comment::doctroComments($this->doc->id);
        $place = $this->doc->Places()->first();
        if (isset($place->detail[Place::DETAIL_KEY_NUMBERS])) {
            $this->fetchData['tel'] = implode(',', $place->detail[Place::DETAIL_KEY_NUMBERS]);
        }
        if (isset($place->detail[Place::DETAIL_ADDRESS])) {
            $this->fetchData['address'] = $place->detail[Place::DETAIL_ADDRESS];
        }
        if (isset($place->detail[Place::DETAIL_KEY_LOCATION])) {
            $this->fetchData['navigate'] = "https://maps.google.com/maps?daddr=" . $place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LAT] . ',' . $place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LNG];
        }
        $this->fetchData['modalStep'] = 0 ;
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
