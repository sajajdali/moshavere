<?php

namespace Modules\AppointmentUser\Service;

use Carbon\Carbon;
use Modules\Absence\app\Models\Absence;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSettingTime;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class AppointmentUserService
{
    private $app;

    /**
     * @param $app
     */
    public function __construct($app = null)
    {
        if ($app == null) {
            $app = app();
        }
        $this->app = $app;
    }


    public function isTimeRangeAvailable($newTimeRange, $existingTimeRanges)
    {
        foreach ($existingTimeRanges as $existingTimeRange) {
            $existingFrom = strtotime($existingTimeRange['from']);
            $existingUntil = strtotime($existingTimeRange['until']);
            $newFrom = strtotime($newTimeRange['from']);
            $newUntil = strtotime($newTimeRange['until']);

            // Check for overlap
            if (($newFrom >= $existingFrom && $newFrom < $existingUntil) ||
                ($newUntil > $existingFrom && $newUntil <= $existingUntil) ||
                ($newFrom <= $existingFrom && $newUntil >= $existingUntil)) {

                // Calculate overlapped time in minutes
                $overlapStart = max($existingFrom, $newFrom);
                $overlapEnd = min($existingUntil, $newUntil);
                $overlapTime = ($overlapEnd - $overlapStart) / 60; // Convert to minutes

                return [
                    'status' => true,
                    'existingFrom' => $existingTimeRange['from'],
                    'existingUntil' => $existingTimeRange['until'],
                    'overLapTime' => $overlapTime
                ];
            }
        }
        return [
            'status' => false,
            'overLapTime' => 0 // No overlap, so overlapped time is 0
        ];
    }


    public function listAppointments(AppointmentSetting $appointmentSetting , array $details = [])
    {
        // Get the date range for which you want to fetch appointments and available slots
        $specialDaySelected = false;
        if (isset($details['specialDay'])){
            if(array_key_exists('specialDay' , $details)){
                $specialDaySelected = true;
                $startDate = Carbon::parse($details['specialDay']);
                $endDate = $startDate->copy()->addDay(); // Adjust the number of days as needed
            }

            elseif (array_key_exists('completeDays' , $details)){
                $startDate = Carbon::parse($details['specialDay']);
                $endDate = $startDate->copy()->addDays($details['numberDays']);
            }

        }

        if (!$specialDaySelected){
            $startDate = Carbon::today()->subDays(15);
            $endDate = Carbon::today()->addDays($appointmentSetting->max_day_active ?? 90); // Adjust the number of days as needed
        }

        // Fetch appointments for the week
        $doctorId = $appointmentSetting->user->id;
        $appointments = AppointmentUser::where('doctor_id', $doctorId)
            ->whereBetween('date_visit', [$startDate, $endDate])
            ->orderBy('start_time')
            ->get();

        $appointments = $appointments->sortByDesc(function ($appointment) {
            // If there's no appointment with the same start_time, it should have the highest priority
            $maxEndTime = AppointmentUser::where('start_time', $appointment->start_time)
                ->where('id', '<>', $appointment->id) // Exclude the current appointment
                ->max('end_time');

            // Compare the current appointment's end_time with the maximum end_time
            return $appointment->end_time > $maxEndTime;
        });


        // Fetch appointment settings for the doctor
        $appointmentSettings = AppointmentSetting::where('user_id', $doctorId)->first();

        // Initialize the output array
        $output = [];

        // Iterate over the week starting from today

        $firstDayInLog = null;
        $firstEmptyDay = null;
        for ($currentDate = $startDate; $currentDate->lte($endDate); $currentDate->addDay()) {
            $year = verta($currentDate)->year;
            $month = verta($currentDate)->month;
            $day = verta($currentDate)->day;

            if ($firstDayInLog == null){
                $firstDayInLog = $currentDate;
            }

            // Initialize the day's output
            $dayOutput = [
                'status' => true,
                'empty_appoints' => 0,
                'times' => [],
            ];

            if ($appointmentSettings) {
                // Get the time for each visit in minutes
                $timeForVisit = $appointmentSettings->time_for_visit;

                //  check special date
                $attendanceTimes = $appointmentSettings->times()
                    ->whereDate('special_date', $currentDate->toDateString())
                    ->get();

                // Fetch attendance times for the day using the relationship
                if ($attendanceTimes->isEmpty()) {
                    $attendanceTimes = $appointmentSettings->times()
                        ->where('day_number', $currentDate->dayOfWeek)
                        ->get();
                }

                // Iterate over attendance times

                //list appointments
                foreach ($appointments as $appointment) {
                    if (Carbon::parse($appointment->date_visit)->isSameDay($currentDate)) {

                        // remove item in collection
                        $appointments = $appointments->filter(function ($app) use ($appointment) {
                            // Return true to keep the item, false to remove it
                            return $appointment->id != $app->id; // adjust condition accordingly
                        });
                        // remove item in collection

                        $dayOutput['times'][] = [
                            'status' => false,
                            'from' => $appointment->start_time,
                            'until' => $appointment->end_time,
                            'appointment_user_id' => $appointment->id,
                        ];
                    }
                }
                usort($dayOutput['times'], function ($a, $b) {
                    return strtotime($a['from']) - strtotime($b['from']);
                });

                //list appointments
                foreach ($attendanceTimes as $attendanceTime) {

                    // In case of non-attendance
                    $absence = Absence::whereDate('start_at', '<=', $currentDate)
                        ->whereDate('end_at', '>=', $currentDate)->get();
                    if ($absence->isNotempty()) {
                        $dayOutput['absence'] = true;
                        $dayOutput['status'] = false;
                        $dayOutput['empty_appoints'] = 0;
                        break;
                    }
                    // In case of non-attendance

                    $startTime = Carbon::parse($attendanceTime->start_at);
                    $endTime = Carbon::parse($attendanceTime->end_at);
                    $addTime = $timeForVisit;

                    // Add time slots for each attendance time

                    while ($startTime->lt($endTime)) {
                        // Let's check that the time has not over

                        $overlaps = $this->isTimeRangeAvailable([
                            'from' => $startTime->toTimeString(),
                            'until' => $startTime->copy()->addMinutes($timeForVisit)
                        ] , $dayOutput['times']);

                        if ($overlaps['status'] == false) {
                            $until = $startTime->copy()->addMinutes($timeForVisit);
                            if ($endTime->lt($until)){
                                $dayOutput['times'][] = [
                                    'status' => false,
                                    'from' => $startTime->toTimeString(),
                                    'until' => $endTime->toTimeString(),
                                    'gap'   => true,
                                ];
                            } else {

                                $dayOutput['times'][] = [
                                    'status' => true,
                                    'from' => $startTime->toTimeString(),
                                    'until' => $until->toTimeString(),
                                ];
                                $dayOutput['empty_appoints']++;
                                $startTime = $until->subMinutes($timeForVisit);

                                // set first empty day in log
                                if (!$firstEmptyDay){
                                    $firstEmptyDay = [
                                        'day'   => $currentDate->toDateString(),
                                        'from' => $startTime->copy()->toTimeString(),
                                        'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString()
                                    ];
                                }
                            }

                        } else {
                            if ($overlaps['overLapTime'] != 0 && $overlaps['overLapTime'] < $timeForVisit) {

                                $startTime->addMinutes($overlaps['overLapTime']);
                                $overlapsAgain = $this->isTimeRangeAvailable([
                                    'from' => $startTime->toTimeString(),
                                    'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                ] , $dayOutput['times']);

                                if ($overlapsAgain['status'] == false){

                                    $until = $startTime->copy()->addMinutes($timeForVisit);
                                    if ($endTime->lt($until)){

                                        $dayOutput['times'][] = [
                                            'status' => false,
                                            'from' => $startTime->copy()->toTimeString(),
                                            'until' => $endTime->toTimeString(),
                                            'gap' => true
                                        ];
                                    } else {

                                        $dayOutput['times'][] = [
                                            'status' => true,
                                            'from' => $startTime->copy()->toTimeString(),
                                            'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                        ];
                                        $dayOutput['empty_appoints']++;

                                        // set first empty day in log
                                        if (!$firstEmptyDay){
                                            $firstEmptyDay = [
                                                'day'   => $currentDate->toDateString(),
                                                'from' => $startTime->copy()->toTimeString(),
                                                'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString()
                                            ];
                                        }

                                    }


                                } else {
                                    $startTime->subMinutes($overlaps['overLapTime']);

                                    $getLastOverLapsTime = $this->isTimeRangeAvailable([
                                        'from' => $startTime->toTimeString(),
                                        'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                    ] , $dayOutput['times']);

                                    $dayOutput['times'][] = [
                                        'status' => false,
                                        'from' => $startTime->copy()->toTimeString(),
                                        'until' => $getLastOverLapsTime['existingFrom'],
                                        'gap' => true
                                    ];
                                }

                            }
                        }
                        $startTime->addMinutes($addTime);
                    }
                }
                if ($dayOutput['empty_appoints'] == 0) {
                    $dayOutput['status'] = false;
                    $dayOutput['empty_appoints'] = 0;
                }

                // Fill the slots with appointments
            } else {
                $dayOutput['status'] = false; // Appointment settings not found for the doctor
            }

            usort($dayOutput['times'], function ($a, $b) {
                return strtotime($a['from']) - strtotime($b['from']);
            });

            // Sorting the array
            $output['report'] = [
                'active_inPerson' => $appointmentSettings['detail']['visit_type_inPerson'] ?? false,
                'active_voip' => $appointmentSettings['detail']['visit_type_voip'] ?? false,
                'active_online' => $appointmentSettings['detail']['visit_type_online'] ?? false,
                'first_day' =>  $firstDayInLog->toDateString(),
                'last_day' => $currentDate?->toDateString(),
                'first_empty_day' => $firstEmptyDay,
            ];
            $output['data'][$year][$month][$day] = $dayOutput;

            if ($specialDaySelected){
                break;
            }
        }

        // Now $output contains the formatted output for the week with filled appointments and empty slots arranged
        // You can return this array to your view
        return $output;
    }


    /*
     * current sections
     *
    public function listAppointments($doctorId)
    {
        // Get the date range for which you want to fetch appointments and available slots
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays(15); // Adjust the number of days as needed

        // Fetch appointments for the week
        $appointments = AppointmentUser::where('doctor_id', $doctorId)
            ->whereBetween('date_visit', [$startDate, $endDate])
            ->orderBy('start_time')
            ->get();

        $appointments = $appointments->sortByDesc(function ($appointment) {
            // If there's no appointment with the same start_time, it should have the highest priority
            $maxEndTime = AppointmentUser::where('start_time', $appointment->start_time)
                ->where('id', '<>', $appointment->id) // Exclude the current appointment
                ->max('end_time');

            // Compare the current appointment's end_time with the maximum end_time
            return $appointment->end_time > $maxEndTime;
        });


        // Fetch appointment settings for the doctor
        $appointmentSettings = AppointmentSetting::where('user_id', $doctorId)->first();

        // Initialize the output array
        $output = [];

        // Iterate over the week starting from today
        for ($currentDate = $startDate; $currentDate->lte($endDate); $currentDate->addDay()) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $day = $currentDate->day;

            // Initialize the day's output
            $dayOutput = [
                'status' => true,
                'empty_appoints' => 0,
                'times' => [],
            ];

            if ($appointmentSettings) {
                // Get the time for each visit in minutes
                $timeForVisit = $appointmentSettings->time_for_visit;

                // Fetch attendance times for the day using the relationship
                $attendanceTimes = $appointmentSettings->times()
                    ->where('day_number', $currentDate->dayOfWeek)
                    ->get();

                // Iterate over attendance times

                $lastUntilTime = null;
                foreach ($attendanceTimes as $attendanceTime) {

                    $startTime = Carbon::parse($attendanceTime->start_at);
                    if ($lastUntilTime == null){
                        $lastUntilTime = $startTime;
                    }

                    $endTime = Carbon::parse($attendanceTime->end_at);
                    $addTime = $timeForVisit;

                    // Add time slots for each attendance time
                    while ($startTime->lt($endTime)) {
                        $found = false;


                        foreach ($appointments as $appointment) {
                            $endTimeForVisit = $startTime->copy()->addMinutes($timeForVisit);

                            $appointmentStartTime = Carbon::parse($appointment->start_time);
                            $appointmentEndTime = Carbon::parse($appointment->end_time);
                            if (
                                Carbon::parse($appointment->date_visit)->isSameDay($currentDate) &&
                                (
                                    ($appointmentStartTime->copy()->addSecond()->between($startTime, $endTimeForVisit))
                                    ||
                                    ($appointmentEndTime->copy()->addSecond()->between($startTime, $endTimeForVisit))
                                    ||
                                    ($appointmentStartTime->lte($startTime) && $appointmentEndTime->gte($endTimeForVisit))
                                )
                            ) {

                                $found = true;
                                // remove item in collection
                                $appointments = $appointments->filter(function ($app) use ($appointment) {
                                    // Return true to keep the item, false to remove it
                                    return $appointment->id != $app->id; // adjust condition accordingly
                                });
                                // remove item in collection

                                // remove all appointment user when start_at be smaller than the completion time of the current turn that is inside the loop
                                $appointments = $appointments->filter(function ($app) use ($appointment , $currentDate) {
//                                    // Return true to keep the item, false to remove it
                                        return $app->start_time < $appointment->end_time ; // adjust condition accordingly
                                });

                                // If the appointmentUser start time is greater than the set start time
                                if ( ($appointmentStartTime->lte($startTime) && $appointmentEndTime->gte($endTimeForVisit))){
                                    $addTime  = $appointmentEndTime->diffInMinutes($startTime);
                                }

                                else {
                                    $addTime  = $appointmentEndTime->diffInMinutes($appointmentStartTime);
                                }


                                // If a gap has been created in times
                                $possibleGap = $appointmentStartTime->diffInMinutes($startTime);
                                if ($possibleGap < $timeForVisit && $possibleGap > 0){
                                    $fromTime = $lastUntilTime->gte($startTime) ? $lastUntilTime : $startTime;
                                    $fromTime = $fromTime->gte($appointmentStartTime) ? $startTime : $fromTime;
                                    $dayOutput['times'][] = [
                                        'status' => false,
                                        'from' => $fromTime->toTimeString(),
                                        'until' => $appointment->start_time,
                                        'gap'   => true
                                    ];
                                    $addTime += $possibleGap;
                                }
                                // If a gap has been created in times


                                $lastUntilTime = $appointmentEndTime;
                                $dayOutput['times'][] = [
                                    'status' => false,
                                    'from' => $appointment->start_time,
                                    'until' => $appointment->end_time,
                                    'appointment_user_id' => $appointment->id,
                                ];
                                break;
                            }
                        }
                        if (!$found) {

                            // Checks whether a long turn in this hour has already been taken or not

                            if ( $startTime->greaterThanOrEqualTo($lastUntilTime)) {

                                // Let's check that the time has not over
                                if ( $startTime->diffInMinutes($endTime) < $timeForVisit){
                                    $dayOutput['times'][] = [
                                        'status' => false,
                                        'from' => $startTime->toTimeString(),
                                        'until' => $endTime->toTimeString(),
                                    ];
                                } else {
                                    $lastUntilTime = $startTime->copy()->addMinutes($timeForVisit);
                                    $dayOutput['times'][] = [
                                        'status' => true,
                                        'from' => $startTime->toTimeString(),
                                        'until' => $lastUntilTime->toTimeString(),
                                    ];
                                    $dayOutput['empty_appoints']++;
                                }

                            }

                            else {
                                $gap = $startTime->diffInMinutes($lastUntilTime);
                                $gapLenght = $lastUntilTime->diffInMinutes($startTime->copy()->addMinutes($gap));
                                if ($gap < $timeForVisit && $gap > 0 && $gapLenght > 0) {
                                    $dayOutput['times'][] = [
                                        'status' => false,
                                        'from' => $lastUntilTime->toTimeString(),
                                        'until' => $startTime->copy()->addMinutes($gap)->toTimeString(),
                                        'gap'   => true
                                    ];
                                }
                                if ($gap < $timeForVisit && $gap  > 0){
                                    $addTime = $gap;
                                }
                            }

                        }
                        $startTime->addMinutes($addTime);
                        $addTime = $timeForVisit;
                    }
                }

                // The appointments that are on this day and are after the set hours
                foreach ($appointments as $appointment) {
                    $dayOutput['times'][] = [
                        'status' => false,
                        'from' => $appointment->start_time,
                        'until' => $appointment->end_time,
                        'appointment_user_id' => $appointment->id,
                    ];
                    $appointments = $appointments->filter(function ($app) use ($appointment) {
                        // Return true to keep the item, false to remove it
                        return $appointment->id != $app->id; // adjust condition accordingly
                    });
                }

                // Fill the slots with appointments
            } else {
                $dayOutput['status'] = false; // Appointment settings not found for the doctor
            }


            usort($dayOutput['times'], function ($a, $b){
                return strtotime($a['from']) - strtotime($b['from']);
            });

            // Sorting the array
            $output[$year][$month][$day] = $dayOutput;
        }

        // Now $output contains the formatted output for the week with filled appointments and empty slots arranged
        // You can return this array to your view
        return $output;
    }
    */


}
