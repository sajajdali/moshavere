<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class ListOfAvailableDay extends Component
{
    public $specificDayDate;
    public array $fethData = [];


    public function GotoSpecificDay()
    {
        $date = Verta::parse($this->specificDayDate)->toCarbon()->timestamp;
        return redirect()->route('admin.appointment.add.specificday', ['date' => $date]);
    }
    public function GotoAppointmentList($time, $day)
    {
        $timeArray = explode(':', $day);
        $passedDate =  verta(Carbon::parse($time))->format('Y-m-d');
        $passedHour = Carbon::createFromTimestamp((int)$time)->setTime($timeArray[0], $timeArray[1])->timestamp;

        return redirect()->route('admin.appointment.add.specificday', ['appId' => $this->fethData['appointmentSetting'], 'date' => $passedDate, 'time' => $passedHour]);
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
        $maxDay = 7;
        $DaysDisplayed = 0;
        foreach ($listOfAppointment['data'] as $yeay => $day) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($day as $month => $appointments) {
                if ($month < $isMonth) {
                    continue;
                }
                foreach ($appointments as $day => $appointment) {
                    if ($day < $isDay || $appointment['empty_appoints'] <= 0 || $appointment['status'] == false) {
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
        $serviceId =  request()->get('sectionId');
        $doctorId  =  request()->get('doctorId');
        $placeId   =  request()->get('placeId');
        //check for special setting for special section
        $appointmentSetting = AppointmentSetting::where('service_id', $serviceId)
            ->where('place_id', $placeId)
            ->first();
        //check for general setting
        if (empty($appointmentSetting)) {
            $appointmentSetting = AppointmentSetting::where('user_id', $doctorId)->first();
        }
        // redirect user if setting dosent exist
        if (empty($appointmentSetting)) {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'لطفا ابتدا تنظیمات حضور پزشک را ثبت کنید');
        }
        $listOfAppointment = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
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
