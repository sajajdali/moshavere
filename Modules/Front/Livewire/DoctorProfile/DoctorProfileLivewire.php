<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\Front\app\Models\Comment;
use Modules\Service\app\Models\Service;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

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
            if (! isset($this->form['place']) || empty($this->form['place'])) {
                $this->fetchData['places'] = $this->doc->places;
                $this->fetchData['modalStep'] = 1;
                if (isset($this->fetchData['services'])) {
                    unset($this->fetchData['services']);
                }
                if (isset($this->fetchData['place'])) {
                    unset($this->fetchData['place']);
                }
            }
            return  $this->lunchModal();
        }
        if ($this->doc->services()->count() > 1) {
            $this->fetchData['services'] = $this->doc->services;
            return $this->lunchModal();
        }

        // if less than one service exist , redirect to appointment days list
        $this->redirectToAppointmentDays($this->doc->id, $this->doc->places()->first()->id, $this->doc->services()->first()->id);
    }
    public function lunchModal()
    {
        return $this->dispatch('lucnhModal', true);
    }
    private function redirectToAppointmentDays($doctor_id, $place_id, $service_id, $segment = null)
    {
        $param = [
            'doctor_id'     => $doctor_id,
            'place_id'      => $place_id,
            'service_id'    => $service_id

        ];
        if (!empty($segment)) {
            $param['segment'] = $segment;
        }
        return redirect()->route(
            'front.setAppointment.days',
            $param
        );
    }
    public function modalSubmit()
    {
        if ($this->fetchData['modalStep'] == 1) {
            $this->fetchData['services'] = $this->doc->services;
            if (isset($this->form['service']) && !empty($this->form['service'])) {
                // user selected service on privous page
                $this->serviceHasSelected();
                $this->fetchData['modalStep']++;
            } else {
                if ($this->doc->services()->count() <= 1) {
                    $this->redirectToAppointmentDays(
                        $this->doc->id,
                        $this->form['place'],
                        $this->doc->services()->first()->id
                    );
                }
                $this->fetchData['modalStep']++;
            }
        } elseif ($this->fetchData['modalStep'] == 2) {
            if (count($this->form['segment']) > 1) {
                foreach ($this->form['segment'] as $segmentId => $status) {
                    if ($status) {
                        $this->form['selectedSegmentForRoute'][] = $segmentId;
                    }
                }
            } else {
                $this->form['selectedSegmentForRoute'] = $this->form['segment'];
            }
            $this->redirectToAppointmentDays(
                $this->doc->id,
                $this->form['place'],
                $this->form['service'],
                $this->form['selectedSegmentForRoute'],
            );
        }
    }

    public function serviceHasSelected()
    {
        if (isset($this->form['service'])) {
            $app_setting = AppointmentSetting::where('user_id', $this->doc->id)->where('place_id', $this->form['place'])->where('service_id', $this->form['service'])->first();
            if (empty($app_setting)) {
                $app_setting = AppointmentSetting::where('user_id', $this->doc->id)->whereNull('place_id')->whereNull('service_id')->first();
            }
            if ($app_setting->segments->count()) {
                $segment = $app_setting->segments()->first();
                if ($segment->multiple_choice == "1") {
                    // segment has one choise
                    $this->fetchData['multiple_choice'] = false;
                } else {
                    // segment has multiple choise
                    $this->fetchData['multiple_choice'] = true;
                }
                $this->fetchData['segments'] = $segment->items()->orderBy('priority')->get();
            } else {
                $this->redirectToAppointmentDays(
                    $this->doc->id,
                    $this->form['place'],
                    $this->form['service']
                );
            }
        }
    }


    // check is user redirect to this page with service_id and place_id
    private function routeHasServiceOrPlace()
    {
        if (request()->has('service_id')) {
            $santetizeService = htmlspecialchars(request()->input('service_id'), ENT_QUOTES, 'UTF-8');
            $this->form['service'] =  Service::find($santetizeService)?->id ?? null;
        }
        if (request()->has('place_id')) {
            $santetizeService = htmlspecialchars(request()->input('place_id'), ENT_QUOTES, 'UTF-8');
            $place =   Place::find($santetizeService) ?? null;
            if (isset($place) && !empty($place)) {
                $this->form['place'] = $place->id ;
                $this->form['place_name'] = $place->title ;
                $this->fetchData['services'] = $this->doc->services;
                $this->fetchData['modalStep'] = 2;
            }
        }
        // service has selected
        if (isset($this->form['service']) && !isset($this->form['place_id'])) {
            $this->fetchData['modalStep'] = 1;
        }
        if (isset($this->form['service']) && isset($this->form['place_id'])) {
            $this->serviceHasSelected();
            $this->fetchData['modalStep'] = 2;
            $this->lucnhModal();
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
        $this->fetchData['modalStep'] = 1;
        $this->fetchData['is_app_available'] =  $this->doc->isDoctorActive();

        $this->routeHasServiceOrPlace();
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
