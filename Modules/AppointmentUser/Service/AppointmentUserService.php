<?php

namespace Modules\AppointmentUser\Service;

use App\Event;
use Carbon\Carbon;
use Modules\Absence\app\Models\Absence;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSettingTime;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\model\MainUserModel;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\Setting\Enum\SettingKeyEnum;
use Verta;

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


    public function isTimeRangeAvailable($from, $until, $existingTimeRanges)
    {
        foreach ($existingTimeRanges as $existingTimeRange) {
            $existingFrom = strtotime($existingTimeRange['from']);
            $existingUntil = strtotime($existingTimeRange['until']);
            $newFrom = strtotime($from);
            $newUntil = strtotime($until);
            $betweenPatients = $existingTimeRange['type'] ?? 1;

            // Check for overlap
            if ($betweenPatients == 1 &&
                (
                    ($newFrom >= $existingFrom && $newFrom < $existingUntil) ||
                    ($newUntil > $existingFrom && $newUntil <= $existingUntil) ||
                    ($newFrom <= $existingFrom && $newUntil >= $existingUntil)
                )
            ) {

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


    public function listAppointments(AppointmentSetting $appointmentSetting, array $details = [])
    {
        // Get the date range for which you want to fetch appointments and available slots
        $specialDaySelected = false;
        if (isset($details['specialDay'])) {
            if (array_key_exists('specialDay', $details)) {
                $specialDaySelected = true;
                $startDate = Carbon::parse($details['specialDay']);
                $endDate = $startDate->copy()->addDay(); // Adjust the number of days as needed
            } elseif (array_key_exists('completeDays', $details)) {
                $startDate = Carbon::parse($details['specialDay']);
                $endDate = $startDate->copy()->addDays($details['numberDays']);
            }

        }

        if (!$specialDaySelected) {
//            $startDate = Carbon::today()->subDays(20);
            $startDate = Carbon::today()->addDays(4);
            $appointmentSetting->max_day_active = 5;
            $endDate = Carbon::today()->addDays($appointmentSetting->max_day_active ?? 90); // Adjust the number of days as needed
        }

        // get holidays
        $holidays = Event::whereBetween('date', [$startDate, $endDate])->where('is_holiday', '1')->get();

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

        // List of attendance times
        $appointmentSettingTimes = $appointmentSettings->times()->get();

        // Initialize the output array
        $output = [];

        // Iterate over the week starting from today
        $firstDayInLog = $firstEmptyDay = $lastDayInLog = null;

        for ($currentDate = $startDate; $currentDate->lte($endDate); $currentDate->addDay()) {
            $year = verta($currentDate)->year;
            $month = verta($currentDate)->month;
            $day = verta($currentDate)->day;

            if ($appointmentSettings->last_day_active) {
                if (Carbon::parse($appointmentSettings->last_day_active)->lt($currentDate)) {
                    break;
                }
            }

            // Initialize the day's output
            $dayOutput = [
                'status' => true,
                'day_number' => verta($currentDate)->format("l m/d"),
                'day_number_gmt' => $currentDate->toDateString(),
                'is_holiday' => false,
                'empty_appoints' => 0,
                'times' => [],
            ];

            if ($appointmentSettings->count()) {
                // Get the time for each visit in minutes
                $timeForVisit = $appointmentSettings->time_for_visit;

                //  check special date
                $checkHoliday = false;
                $attendanceTimes = $appointmentSettingTimes->filter(function ($appointmentTime) use ($currentDate) {
                    return $appointmentTime->special_date == $currentDate->toDateString();
                });

                // Fetch attendance times for the day using the relationship
                if ($attendanceTimes->isEmpty()) {
                    $checkHoliday = true;
                    $attendanceTimes = $appointmentSettingTimes->filter(function ($appointmentTime) use ($currentDate) {
                        return $appointmentTime->day_number->value == $currentDate->copy()->addDay()->dayOfWeek;
                    });
                }

                //list appointments
                foreach ($appointments as $appointment) {
                    if (Carbon::parse($appointment->date_visit)->isSameDay($currentDate)) {

                        // remove item in collection
                        $appointments = $appointments->filter(function ($app) use ($appointment) {
                            return $appointment->id != $app->id; // adjust condition accordingly
                        });

                        $dayOutput['times'][] = [
                            'status' => false,
                            'from' => $appointment->start_time,
                            'type' => $appointment->type->value,
                            'until' => $appointment->end_time,
                            'appointment_user_id' => $appointment->id,
                        ];
                    }
                }

                // sort appointments list from time_from
                usort($dayOutput['times'], function ($a, $b) {
                    return strtotime($a['from']) - strtotime($b['from']);
                });

                //list appointments
                foreach ($attendanceTimes as $attendanceTime) {

                    // In case of non-attendance
                    $serviceId = $appointmentSettings->service_id;
                    $placeId = $appointmentSettings->place_id;

                    $absence = $appointmentSetting->user->absence()
                        ->whereDate('start_at', '<=', $currentDate)
                        ->whereDate('end_at', '>=', $currentDate);

                    if ($serviceId) {
                        $absence->where('service_id', $serviceId);
                    }
                    if ($placeId) {
                        $absence->where('place_id', $placeId);
                    }
                    $absence = $absence->get();


                    if ($absence->isNotempty()) {
                        $dayOutput['absence'] = true;
                        $dayOutput['status'] = false;
                        $dayOutput['empty_appoints'] = 0;
                        break;
                    }
                    // In case of non-attendance

                    // check holiday
                    if ($holidays->contains('date', $currentDate->toDateString())) {
                        $dayOutput['is_holiday'] = true;
                        $dayOutput['status'] = true;
                        $dayOutput['empty_appoints'] = 0;
                        if ($checkHoliday) {
                            $dayOutput['status'] = false;
                            break;
                        }
                    }

                    if ($firstDayInLog == null) {
                        $firstDayInLog = clone $currentDate;
                    }

                    $startTime = Carbon::parse($attendanceTime->start_at);
                    $endTime = Carbon::parse($attendanceTime->end_at);

                    // Add time slots for each attendance time

                    while ($startTime->lt($endTime)) {
                        // Let's check that the time has not over

                        $overlaps = $this->isTimeRangeAvailable($startTime->toTimeString(), $startTime->copy()->addMinutes($timeForVisit), $dayOutput['times']);



                        if ($overlaps['status'] == false) {
                            $until = $startTime->copy()->addMinutes($timeForVisit);

                            // handle end time visit
                            if ($endTime->lt($until)) {

                                $dayOutput['times'][] = [
                                    'status' => false,
                                    'from' => $startTime->toTimeString(),
                                    'until' => $endTime->toTimeString(),
                                    'gap' => true,
                                ];
                            } // handle end time visit

                            else {

                                $dayOutput['times'][] = [
                                    'status' => !$currentDate->isPast(),
                                    'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                    'from' => $startTime->toTimeString(),
                                    'until' => $until->toTimeString(),
                                ];
                                $dayOutput['empty_appoints']++;
                                $startTime = $until->subMinutes($timeForVisit);

                                // set first empty day in log
                                if (!$firstEmptyDay && !$currentDate->isPast()) {
                                    $firstEmptyDay = [
                                        'day' => verta($currentDate)->format("Y-m-d"),
                                        'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                        'from' => $startTime->copy()->toTimeString(),
                                        'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString()
                                    ];
                                }
                            }

                        } else {
                            if ($overlaps['overLapTime'] != 0 && $overlaps['overLapTime'] < $timeForVisit) {

                                $startTime->addMinutes($overlaps['overLapTime']);
                                $overlapsAgain = $this->isTimeRangeAvailable($startTime->toTimeString(), $startTime->copy()->addMinutes($timeForVisit)->toTimeString(), $dayOutput['times']);

                                if ($overlapsAgain['status'] == false) {

                                    $until = $startTime->copy()->addMinutes($timeForVisit);
                                    if ($endTime->lt($until)) {

                                        $dayOutput['times'][] = [
                                            'status' => false,
                                            'from' => $startTime->copy()->toTimeString(),
                                            'until' => $endTime->toTimeString(),
                                            'gap' => true
                                        ];
                                    } else {

                                        $dayOutput['times'][] = [
                                            'status' => !$currentDate->isPast(),
                                            'timestamp' => $startDate->copy()->timestamp,
                                            'from' => $startTime->copy()->toTimeString(),
                                            'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                        ];
                                        $dayOutput['empty_appoints']++;

                                        // set first empty day in log
                                        if (!$firstEmptyDay && !$currentDate->isPast()) {
                                            $firstEmptyDay = [
                                                'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                                'day' => verta($currentDate)->format("Y-m-d"),
                                                'from' => $startTime->copy()->toTimeString(),
                                                'until' => $startTime->copy()->addMinutes($timeForVisit)->toTimeString()
                                            ];
                                        }
                                    }

                                } else {
                                    $startTime->subMinutes($overlaps['overLapTime']);

                                    $getLastOverLapsTime = $this->isTimeRangeAvailable($startTime->toTimeString(), $startTime->copy()->addMinutes($timeForVisit)->toTimeString(), $dayOutput['times']);
                                    $startTimeOverLap = $getLastOverLapsTime['existingUntil'] == $overlapsAgain['existingUntil'] ? $startTime->copy()->toTimeString() : $overlaps['existingUntil'];

                                    $dayOutput['times'][] = [
                                        'status' => false,
                                        'from' => $startTimeOverLap,
                                        'until' => $overlapsAgain['existingFrom'],
                                        'gap' => true
                                    ];
                                }

                            }
                        }
                        $startTime->addMinutes($timeForVisit);
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

            $output['data'][$year][$month][$day] = $dayOutput;
            $lastDayInLog = $currentDate->toDateString();

            if ($specialDaySelected) {
                break;
            }
        }
        $paymentStatus = false;
        $notPayinStatus = $paymentPrice = $paymentOnline = $paymentVoip = null;
        if (isset($details['payment']['price']) && $details['payment']['price'] > 0) {
            $paymentStatus = true;
            $paymentPrice = $details['payment']['price'] ?? null;
            $paymentOnline = $details['payment']['online']['status'] ?? null;
            $paymentVoip = $details['payment']['voip']['status'] ?? null;
            $notPayinStatus = $details['payment']['online']['notPayinStatus'] ?? null;
        }
        $output['report'] = [
            'time_for_visit' => $appointmentSettings->time_for_visit,
            'payment' => [
                'status' => $paymentStatus,
                'price' => $paymentPrice,
                'paymentOnline' => $paymentOnline,
                'paymentVoip' => $paymentVoip,
                'notPayinStatus' => $notPayinStatus,
            ],
            'active_inPerson' => $appointmentSettings['detail']['visit_type_inPerson'] ?? false,
            'active_voip' => $appointmentSettings['detail']['visit_type_voip'] ?? false,
            'active_online' => $appointmentSettings['detail']['visit_type_online'] ?? false,
            'last_day' => $currentDate?->toDateString(),
            'min_day_active' => $appointmentSettings?->min_day_active,
            'first_empty_dayee' => $firstEmptyDay,
            'last_day_active' => isset($appointmentSettings->last_day_active) ? $appointmentSettings->last_day_active->toDateString() : null,
            'last_day_in_log' => $lastDayInLog,
            'first_day_in_log' => $firstDayInLog?->toDateString(),
            'interference' => $appointmentSettings->interference == 1,
        ];

        // Now $output contains the formatted output for the week with filled appointments and empty slots arranged
        // You can return this array to your view
        return $output;
    }

    public function isAppointmentTimeAvailable($startDateTime, $endDateTime, $dateVisit, AppointmentSetting $appointmentSetting)
    {
        // Check if there are any overlapping appointments
        $existingAppointments = AppointmentUser::where('doctor_id', $appointmentSetting->user_id);
        if (!$appointmentSetting->interference) {
            $existingAppointments->where('appointment_setting_id', $appointmentSetting->id);
        }

        $existingAppointments = $existingAppointments
            ->whereDate('date_visit', $dateVisit)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                        ->where('end_time', '>', $startDateTime);
                });
            })
            ->exists();

        return !$existingAppointments;
    }


    private function paymentstatus(AppointmentSetting $appointmentSetting)
    {
        $deadLineDelete = null;
        $statusPayment = false;
        $forcePayment = false;
        $price = null;

        $detail = $appointmentSetting['detail'];
        if (isset($detail['payment']) && isset($detail['payment']['online']) && $detail['payment']['online']['status']) {
            $statusPayment = true;
            if (isset($detail['payment']['online']['notPayinStatus']) && $detail['payment']['online']['notPayinStatus'] == AppointmentSetting::DETAIL_PAYMENT_NOT_PAY_STATUS_DONT_SUBMIT) {
                $deadLineDelete = Carbon::now()->addHours(4)->toDateTimeString();
                $forcePayment = true;
            }
            $price = $detail['payment']['price'];
        }
        return [
            'status' => $statusPayment,
            'deadline' => $deadLineDelete,
            'force_payment' => $forcePayment,
            'price' => $price
        ];
    }

    public function handleSms(AppointmentUser $appointmentUser)
    {
    }

    private function makeShortLink($appointmentUser)
    {
        $appointmentUser->shortLink()->create([
            'transaction_code' => '323',
            'paid_by' => TransactionPaidEnum::ONLINE,
            'status' => TransactionStatusEnum::PENDING,
            'cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE],
            'total_cost' => $this->appointmentUser->details['payment'][AppointmentUser::DETAIL_PAYMENT_PRICE]
        ]);
    }

    public function storeAppointment(AppointmentSetting $appointmentSetting, UserModelAppointment $userModelAppointment, AppointmentModel $appointmentData, $detail = [])
    {
        // check exist appointment
        $detailAppointment = $detail;
        $detailDatabaseDB = [];
        $dateAppointment = Carbon::createFromTimestamp($appointmentData->timestamp);
        $visitDateTime = Carbon::createFromTimestamp($appointmentData->timestamp);
        if ($appointmentData->appointmentVia == AppointmentVia::SELF && $dateAppointment->isPast()) {
            return [
                'status' => false,
                'message' => 'زمان ارسالی برای ثبت نوبت اشتباه است و لطفا مجدد اقدام کنید',
                'route' => 'time'
            ];
        }

        $checkTimeAvailable = $this->isAppointmentTimeAvailable($dateAppointment->toTimeString(), $dateAppointment->copy()->addMinutes($appointmentSetting->time_for_visit)->toTimeString(), $dateAppointment->toDateString(), $appointmentSetting);
        if (!$checkTimeAvailable) {
            return [
                'status' => false,
                'message' => 'زمان انتخابی شما توسط شخصی دیگر پر شده است . لطفا یک زمان دیگر انتخاب کنید',
                'route' => 'time'
            ];
        }

        $paymentstatus = $this->paymentstatus($appointmentSetting);
        // create payment link

        // store appointment
        $appointmentUserModel = [
            'service_id' => $appointmentData->serviceId,
            'place_id' => $appointmentData->placeId,
            'user_id' => $userModelAppointment->userModel->user->id,
            'doctor_id' => $appointmentSetting->user_id,
            'agent_id' => $appointmentData->agentId,
            'operator_id' => $appointmentData->operatorId,
            'tracking_code' => AppointmentUser::generateTrackingCode(),
            'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
            'kind' => $appointmentData->kind,
            'start_time' => $visitDateTime->toTimeString(),
            'end_time' => $visitDateTime->copy()->addMinutes($appointmentSetting->time_for_visit)->toTimeString(),
            'date_visit' => $visitDateTime->toDateTimeString(),
            'user_ip' => ip(),

        ];
        $detailDatabaseDB['payment'] = [
            'status' => false,
        ];

        // handel payment
        $paymentLink = null;
        $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
        if ($appointmentData->appointmentVia == AppointmentVia::SELF && $paymentstatus['status']) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
            $appointmentUserModel['deadline'] = $paymentstatus['deadline'];
            if ($paymentstatus['force_payment']) {
                $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            }
            $detailDatabaseDB['payment'] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['price'],
            ];
        }

        // detailDatabase

        // store question in DB
        if (isset($detailAppointment[AppointmentUser::DETAIL_QUESTION])) {
            $detailDatabaseDB[AppointmentUser::DETAIL_QUESTION] = $detailAppointment[AppointmentUser::DETAIL_QUESTION];
        }

        $appointmentUserModel['details'] = $detailDatabaseDB;

        // store appointment in DB
        $appointmentUser = $appointmentSetting->appointmentUsers()->create($appointmentUserModel);

        // send sms
        $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));

        // create payment link
        if ($appointmentData->appointmentVia == AppointmentVia::SELF && $paymentstatus['status']) {
            $paymentLink = route('appointmentUser.payment', $appointmentUser);
        }
        // handel sms

//        $this->makeShortLink($appointmentUser);

        return [
            'status' => true,
            'message' => 'نوبت با موفقیت برای کاربر ثبت شد',
            'detail' => [
                'tracking_code' => $appointmentUserModel['tracking_code'],
                'appointment_user_id' => $appointmentUser->id,
                'payment_link' => $paymentLink
            ]
        ];


        //        $appointmentLists->where('start_time', '>', $dateAppointment)->where('end_time',);
    }


}
