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
        $date = Verta::parse($this->specificDayDate)->format('Y-m-d');
        return redirect()->route(
            'admin.appointment.add.specificday',
            [
                'serviceId' => $this->fethData['service']->id,
                'placeId'    => $this->fethData['place']->id,
                'appId' => $this->fethData['appointmentSetting'],
                'date' => $date
            ]
        );
    }
    public function GotoAppointmentList($time, $day = null)
    {
        if (!empty($day)) {
            $timeArray = explode(':', $day);
            $passedHour = Carbon::createFromTimestamp((int)$time)->setTime($timeArray[0], $timeArray[1])->timestamp;
        }
        $passedDate =  verta(Carbon::parse($time))->format('Y-m-d');
        if (!empty($day)) {
            return redirect()->route('admin.appointment.add.specificday', ['serviceId' => $this->fethData['service']->id, 'placeId'    => $this->fethData['place']->id, 'appId' => $this->fethData['appointmentSetting'], 'date' => $passedDate, 'time' => $passedHour]);
        }
        return redirect()->route('admin.appointment.add.specificday', ['serviceId' => $this->fethData['service']->id, 'placeId'    => $this->fethData['place']->id, 'appId' => $this->fethData['appointmentSetting'], 'date' => $passedDate]);
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
    public function mount()
    {
        $serviceId =  request()->route('sectionId');
        $doctorId  =  request()->route('doctorId');
        $placeId   =  request()->route('placeId');

        $this->fethData['service'] = Service::find($serviceId);
        $this->fethData['doctor']  = User::find($doctorId);
        $this->fethData['place']  = Place::find($placeId);

        //check for special setting for special section
        $appointmentSetting = AppointmentSetting::activeSetting()->where('service_id', $this->fethData['service']?->id ?? null)
            ->where('place_id', $this->fethData['place']?->id ?? null)
            ->where('user_id', $this->fethData['doctor']?->id ?? null)
            ->first();

        //check for general setting
        if (empty($appointmentSetting)) {
            $appointmentSetting = AppointmentSetting::activeSetting()->where('user_id', $doctorId)->first();
        }

        // redirect user if setting dosent exist
        if (empty($appointmentSetting)) {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'تنظیمات حضور برای پزشک ثبت نشده است یا غیر فعال است');
        }
        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        $listOfAppointment = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            $appointmentSetting->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
        $this->fethData['firstTreeAvailableAppointment'] =  $this->findFirstTreeAppointment($listOfAppointment);
        $this->fethData['appointmentSetting'] = $appointmentSetting->id;
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.list-of-available-day');
    }
}
