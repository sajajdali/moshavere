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
    private function findFirstTreeAppointment($listOfAppointment)
    {

        $firstTwoEmpty = [];
        $report = $listOfAppointment['report'];
        $mainDaActive = $report['min_day_active'];

        $isDay   = verta()->addDays($mainDaActive)->day;
        $isMonth = verta()->addDays($mainDaActive)->month;
        $isYear  = verta()->addDays($mainDaActive)->year;

        $result = [];
        $maxDay = 6;
        $DaysDisplayed = 0;
        foreach ($listOfAppointment['data'] as $yeay => $day) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($day as $month => $appointments) {

                foreach ($appointments as $day => $appointment) {

                    if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
                        continue;
                    }
                    if ($appointment['empty_appoints'] <= 0 || $appointment['status'] == false) {
                        continue;
                    }
                    $dayNumber = $appointment['day_number_gmt'];
                    $DaysDisplayed++;

                    if ($DaysDisplayed > $maxDay) {
                        break 3;
                    }
                    foreach ($appointment['times'] as $time) {
                        if ($time['status']) {
                            // Increment the counter
                            $result[$dayNumber][] = [
                                'status' => true,
                                'time_stamp' => $time['timestamp'],
                                'from' => $time['from'],
                                'until' => $time['until'],
                            ];
                            if (count($firstTwoEmpty) < 2) {
                                $vertaDateTime = Verta::createTimestamp($time['timestamp']);
                                $firstTwoEmpty[] = [
                                    'persian_date' => $vertaDateTime->format('ساعت H روز l m/d'),
                                    'time_stamp' => $time['timestamp'],
                                    'from' => $time['from'],
                                    'until' => $time['until'],
                                ];
                            }
                            // If two matches are found, break out of the loop
                        }
                    }
                }
            }
        }
        return $result;
    }
    public function mount($doctorId, $sectionId, $placeId)
    {
        $segmentItemId   =  request()->get('segmentItemId', null);
        $this->fethData['service'] = $sectionId;
        $this->fethData['doctor']  = $doctorId;
        $this->fethData['place']   = $placeId;

        $appointmentSetting = AppointmentSetting::SpecialOrGeneralSetting($doctorId->id, $sectionId->id, $placeId->id);

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
        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        if (app()->environment('local')) {
            $details = [];
            if ($segmentItemId != null) {
                $details['segment_time'] =  $this->fethData['segment_time'];
            }
            $listOfAppointment = app('AppointmentUserService')->listAppointments($appointmentSetting, $details);
        } else {
            $details = [];
            if ($segmentItemId != null) {
                $details['segment_time'] =  $this->fethData['segment_time'];
            }
            $listOfAppointment = Cache::rememberForever('appointmentList.' . $appointmentSetting->id . '-' .$this->fethData['segment_time'] , function () use ($appointmentSetting,$details) {
                $listOfAppointment = app('AppointmentUserService')->listAppointments($appointmentSetting, $details);
            });
        }
        $this->fethData['firstTreeAvailableAppointment'] =  $this->findFirstTreeAppointment($listOfAppointment);
        $this->fethData['appointmentSetting'] = $appointmentSetting->id;
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.list-of-available-day');
    }
}
