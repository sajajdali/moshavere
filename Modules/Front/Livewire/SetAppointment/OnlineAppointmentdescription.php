<?php

namespace Modules\Front\Livewire\SetAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;

#[Layout('front::layouts.app')]
#[Title('ثبت نوبت')]
class OnlineAppointmentdescription extends Component
{
    public array $form = [];

    #[Locked]
    public array $fetchData = [];

    public function setOnlineApp()
    {

        $startTime = Carbon::now()->addDay()->timestamp;
        $parameter = [
            'doctor_id'  => $this->fetchData['doc']->id,
            'place_id'   => $this->fetchData['places']->id,
            'service_id' => $this->fetchData['service']->id,
            'start_time' => $startTime,
            'end_time'   => $startTime,
            'isOnline'   => true,
        ];
        if( $this->fetchData['isAppAvailable']) {
            return $this->redirect(route('setAppointment.checkout', $parameter), true);
        }
    }
    public function mount()
    {
        $doc =  request()->input('doctor_id');
        $place =  request()->input('place_id');
        $service =  request()->input('service_id');
        if (!isset($doc) || !isset($place) || !isset($service)) {
            return abort(404);
        }
        if (request()->has('segment')) {
            $route_segments =  request()->input('segment');
            foreach ($route_segments as $item) {
                $this->fetchData['segments'][] =  AppointmentSegmentItem::find($item);
            }
            if (count($this->fetchData['segments']) > 1) {
                $this->fetchData['segment_time'] = 0;
                foreach ($this->fetchData['segments'] as $eachSegTime) {
                    $this->fetchData['segment_time'] += $eachSegTime->time;
                }
            } else {
                $this->fetchData['segment_time'] = $this->fetchData['segments'][0]->time;
            }
        }
        $this->fetchData['doc']      =   User::find($doc);
        $this->fetchData['places']   =   Place::find($place);
        $this->fetchData['service']  =   Service::find($service);

        if (!isset($this->fetchData['doc']) || empty($this->fetchData['places']) ||  empty($this->fetchData['service'])) {
            return abort(404);
        }
        // check if service id not manipulate in url
        $userServices = $this->fetchData['doc']->activeServices()->pluck('id')->toArray();
        $isServiceBelongToUser =  in_array($this->fetchData['service']->id, $userServices);
        $PlaceUser = $this->fetchData['doc']->activePlaces()->pluck('id')->toArray();
        $isPlaceBelongToUser =  in_array($this->fetchData['places']->id, $PlaceUser);
        if ($isServiceBelongToUser != true  || $isPlaceBelongToUser != true) {
            return abort(404);
        }
        $appSetting = AppointmentSetting::where('user_id', $doc)->where(function ($q) use($service) {
            return $q->where('service_id', $service)->orWhereNull('service_id');
        })->where(function ($q) use($place)  {
            return $q->where('place_id', $place)->orWhereNull('place_id');
        })->first();
        $this->fetchData['isAppAvailable'] = true ;
        if(isset($appSetting->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP] )) {
            $maxAppointmentForEachDay = (int) $this->fetchData['appSetting']->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP] ;
            if(AppointmentOnline::where('date_visit', now()->addDay())->count() > $maxAppointmentForEachDay) {
                // appoitment reach their limit  
                $this->fetchData['isAppAvailable'] = false ;
            }
        }
        $this->fetchData['desriptions'] = setting(SettingKeyEnum::APPOINTMENT_ONLINE_DESCRPTION);
    }
    public function render()
    {
        return view('front::livewire.set-appointment.online-appointmentdescription');
    }
}
