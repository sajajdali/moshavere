<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class ListOfAvailableDay extends Component
{
    public $specificDayDate;
    public array $fethData = [];


    public function GotoSpecificDay()
    {
        $this->validate([
            'specificDayDate' => 'required'
        ], [
            'specificDayDate.required' =>  'لطفا تاریخ را انتخاب کنید',
        ]);
        $date = Verta::parse($this->specificDayDate)->format('Y-m-d');
        $parameters = [
            'serviceId' => $this->fethData['service']->id,
            'placeId'    => $this->fethData['place']->id,
            'appId' => $this->fethData['appointmentSetting'],
            'date' => $date
        ];
        if (isset($this->fethData['segment'])) {
            $parameters['segmentItemId'] = $this->fethData['segment'];
        }
        $this->dispatch('show-loading', true);
        return redirect()->route(
            'admin.appointment.add.specificday',
            $parameters
        );
    }
    public function GotoAppointmentList($time, $day = null)
    {
        if (!empty($day)) {
            $timeArray = explode(':', $day);
            $passedHour = Carbon::createFromTimestamp((int)$time)->setTime($timeArray[0], $timeArray[1])->timestamp;
        }
        $passedDate =  verta(Carbon::parse($time))->format('Y-m-d');
        $parameters = [
            'serviceId' => $this->fethData['service']->id,
            'placeId'    => $this->fethData['place']->id,
            'appId' => $this->fethData['appointmentSetting'],
            'date' => $passedDate
        ];
        if (!empty($day)) {
            $parameters['time'] = $passedHour;
        }
        if (isset($this->fethData['segment'])) {
            $parameters['segmentItemId'] = $this->fethData['segment'];
        }
        $this->dispatch('show-loading', true);
        return redirect()->route('admin.appointment.add.specificday', $parameters);
    }
    public function mount($doctorId, $sectionId, $placeId)
    {
        $segmentItemId   =  request()->get('segmentItemId', null);
        $doctor = $doctorId instanceof User ? $doctorId : User::findOrFail($doctorId);
        $service = $sectionId instanceof Service ? $sectionId : Service::findOrFail($sectionId);
        $place = $placeId instanceof Place ? $placeId : Place::findOrFail($placeId);

        $this->fethData['service'] = $service;
        $this->fethData['doctor']  = $doctor;
        $this->fethData['place']   = $place;

        $appointmentSetting = AppointmentSetting::SpecialOrGeneralSetting($doctor->id, $service->id, $place->id);

        // redirect user if setting dosent exist
        if (empty($appointmentSetting)) {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'تنظیمات حضور برای پزشک ثبت نشده است یا غیر فعال است');
        }
        if ($appointmentSetting->segments()->exists()) {
            if ($segmentItemId != null) {
                $this->fethData['segment']  = $segmentItemId;
                $segmentItemId = explode(',', $segmentItemId);
                $segments =  $appointmentSetting->segments->first()->items->whereIn('id', $segmentItemId);
                if (count($segments) > 1) {
                    $this->fethData['segment_time'] = 0;
                    foreach ($segments as $eachSegTime) {
                        $this->fethData['segment_time'] += $eachSegTime->time;
                    }
                } else {
                    $this->fethData['segment_time'] = $segments->first()->time;
                }
            } else {
                session()->flash('error', 'زیر بخش انتخاب  نشده است');
                return redirect()->route('admin.appointment.doctor.list');
            }
        }
        $details = [];
        if ($segmentItemId != null) {
            $details['segment_time'] = $this->fethData['segment_time'];
        }
        $this->fethData['firstTreeAvailableAppointment'] = app('AppointmentUserService')
            ->nearestAvailableDays($appointmentSetting, $details);
        $this->fethData['appointmentSetting'] = $appointmentSetting->id;
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.list-of-available-day');
    }
}
