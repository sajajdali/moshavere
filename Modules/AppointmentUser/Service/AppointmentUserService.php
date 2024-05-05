<?php

namespace Modules\AppointmentUser\Service;

use Modules\Api\Transformers\UserResource;
use Modules\User\Entities\User;
use Verta;
use App\Event;
use Carbon\Carbon;
use App\Models\ShortLink;
use Modules\Absence\app\Models\Absence;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Reminder\app\Models\Reminder;
use Modules\Api\app\Resources\PriceResource;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\Api\app\Resources\Api\SomeoneResource;
use Modules\Reminder\app\Models\AppointmentReminder;
use Modules\AppointmentUser\Enum\model\MainUserModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Events\StoreAppointment;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\app\Events\CancelAppointment;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Events\StoreAppointmentEvent;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSettingTime;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;

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
            $betweenPatients = $existingTimeRange['type'] ?? AppointmentUserTypeEnum::MAIN__APPOINTMENT->value;
            $appointmentStatus = isset($existingTimeRange['app_status']) && in_array($existingTimeRange['app_status'], AppointmentUserStatusEnum::confirmed());

            // Check for overlap
            if (
                $betweenPatients == AppointmentUserTypeEnum::MAIN__APPOINTMENT->value && $appointmentStatus &&
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
                $startDate = Carbon::parse($details['specialDay'])->subDays(20);
                $endDate = $startDate->copy()->addDays($details['specialDay_endDate'] ?? 60); // Adjust the number of days as needed
            } elseif (array_key_exists('specialDays', $details)) {
                $startDate = Carbon::parse($details['specialDays'])->subDays(20);
                $endDate = $startDate->copy()->addDays($appointmentSetting->max_day_active ?? 90); // Adjust the number of days as needed
            } elseif (array_key_exists('completeDays', $details)) {
                $startDate = Carbon::parse($details['specialDay']);
                $endDate = $startDate->copy()->addDays($details['numberDays']);
            }
        }

        if (!$specialDaySelected && !isset($startDate)) {
            $startDate = Carbon::today()->subDays(20);
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

        // Has set a limit on the number that can be received for 1 day
        $maxAppointmentEachDay = (isset($appointmentSetting->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY]) && (int) $appointmentSetting->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY] > 0) ? $appointmentSetting->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY] : null;

        $appointments = $appointments->sortByDesc(function ($appointment) {
            // If there's no appointment with the same start_time, it should have the highest priority
            $maxEndTime = AppointmentUser::where('start_time', $appointment->start_time)
                ->where('id', '<>', $appointment->id) // Exclude the current appointment
                ->max('end_time');

            // Compare the current appointment's end_time with the maximum end_time
            return $appointment->end_time > $maxEndTime;
        });

        // Fetch appointment settings for the doctor
        //        $appointmentSettings = AppointmentSetting::where('user_id', $doctorId)->first();
        $appointmentSettings = $appointmentSetting;

        // List of attendance times
        $appointmentSettingTimes = $appointmentSettings->times()->get();

        // Initialize the output array
        $output = [];

        // Iterate over the week starting from today
        $firstDayInLog = $firstEmptyDay = $lastDayInLog = null;
        for ($currentDate = $startDate; $currentDate->lte($endDate); $currentDate->addDay()) {
            $numberAppointmentsPerDay = 0;
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
                        $numberAppointmentsPerDay++;

                        $dayOutput['times'][] = [
                            'status' => false,
                            'from' => $appointment->start_time,
                            'type' => $appointment->type->value,
                            'app_status' => $appointment->status->value,
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
                    $endTime   = Carbon::parse($attendanceTime->end_at);

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

                                $thisStatus = !$currentDate->isPast();

                                // check max appointment per day
                                if ($maxAppointmentEachDay !== null && (int) $maxAppointmentEachDay > 0) {
                                    if ($numberAppointmentsPerDay  == (int) $maxAppointmentEachDay) {
                                        $thisStatus = false;
                                    }
                                }

                                $dayOutput['times'][] = [
                                    'status' => $thisStatus,
                                    'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                    'from' => $startTime->toTimeString(),
                                    'until' => $until->toTimeString(),
                                ];
                                if ($thisStatus) {
                                    $dayOutput['empty_appoints']++;
                                }
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

                                        // available appointment
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
        $existingAppointments = AppointmentUser::where('doctor_id', $appointmentSetting->user_id)->where('type', AppointmentUserTypeEnum::MAIN__APPOINTMENT)->whereIn('status', AppointmentUserStatusEnum::confirmed());
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


    public function paymentstatus(AppointmentSetting $appointmentSetting)
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
            'price' => PriceResource::make(['price' => $price]),
        ];
    }

    public function handleSms(AppointmentUser $appointmentUser)
    {
    }

    private function makeShortLink($appointmentUser)
    {
        $appointmentUser->shortLink()->create([
            'link_code' => ShortLink::generateShortLinkCode(),
            'link_url'  => route('front.appointment.detail', ['tracking_code' => $appointmentUser->tracking_code]),
        ]);
    }

    private function insertOnlineAppointment(AppointmentUser $appointmentUser): void
    {
        //        $status = $appointmentUser->details[AppointmentUser::DETAIL_APPOINTMENT_VIA] == AppointmentVia::SELF ? AppointmentOnlineStatusEnum::PENDING : AppointmentOnlineStatusEnum::ACCEPTED;
        $status = AppointmentOnlineStatusEnum::ACCEPTED; // TODO : Be temporarily active


        $appointmentUser->online()->create([
            'appointment_setting_id' => $appointmentUser->setting->id,
            'user_id' => $appointmentUser->user->id,
            'doctor_id' => $appointmentUser->doctor->id,
            'tracking_code' => AppointmentOnline::generateTrackingCode(),
            'status' => $status,
            'date_visit' => $appointmentUser->date_visit,
        ]);
    }
    public function storeAppointment(AppointmentSetting $appointmentSetting, UserModelAppointment $userModelAppointment, AppointmentModel $appointmentData, $detail = [])
    {

        // check exist appointment
        $detailAppointment = $detail;
        $detailDatabaseDB = [];
        if ($appointmentData->timestamp == null) {
            $dateAppointment = Carbon::now();
            $visitDateTime = Carbon::now();
        } else {
            $dateAppointment = Carbon::createFromTimestamp($appointmentData->timestamp);
            $visitDateTime = Carbon::createFromTimestamp($appointmentData->timestamp);
        }

        if ($appointmentData->kind == AppointmentUserKindEnum::IN_PERSION && $appointmentData->appointmentVia == AppointmentVia::SELF && $dateAppointment->isPast()) {
            return [
                'status' => false,
                'message' => 'زمان ارسالی برای ثبت نوبت اشتباه است و لطفا مجدد اقدام کنید',
                'route' => 'time'
            ];
        }

        // update user meta
        if ($userModelAppointment->needToUpdate) {
            if ($userModelAppointment->userModel->firstName) {
                $userModelAppointment->userModel->user->first_name = $userModelAppointment->userModel->firstName;
            }
            if ($userModelAppointment->userModel->lastName) {
                $userModelAppointment->userModel->user->last_name = $userModelAppointment->userModel->lastName;
            }
            if ($userModelAppointment->userModel->gender) {
                $userModelAppointment->userModel->user->gender = $userModelAppointment->userModel->gender;
            }
            if ($userModelAppointment->userModel->nationalCode) {
                $userModelAppointment->userModel->user->national_code = $userModelAppointment->userModel->nationalCode;
            }
            if ($userModelAppointment->userModel->birthday) {
                $userModelAppointment->userModel->user->birthday = json_encode($userModelAppointment->userModel->birthday);
            }
            if ($userModelAppointment->userModel->city) {
                $userModelAppointment->userModel->user->city = $userModelAppointment->userModel->city;
            }
        }

        $detailDatabaseDB[AppointmentUser::USER_MODEL] = UserResource::make($userModelAppointment->userModel->user);

        if ($appointmentData->kind == AppointmentUserKindEnum::IN_PERSION && $appointmentData->appointmentVia == AppointmentVia::SELF) {
            $checkTimeAvailable = $this->isAppointmentTimeAvailable($dateAppointment->toTimeString(), $dateAppointment->copy()->addMinutes($appointmentSetting->time_for_visit)->toTimeString(), $dateAppointment->toDateString(), $appointmentSetting);
            if (!$checkTimeAvailable) {
                //                return [
                //                    'status' => false,
                //                    'message' => 'زمان انتخابی شما توسط شخصی دیگر پر شده است . لطفا یک زمان دیگر انتخاب کنید',
                //                    'route' => 'time'
                //                ];
            }
        }

        $paymentstatus = $this->paymentstatus($appointmentSetting);
        // create payment link

        // check if end time has set by admin
        $endTime = $appointmentData->endTime ?? $visitDateTime->copy()->addMinutes($appointmentSetting->time_for_visit)->toTimeString();

        //check for monitoring appointment
        if (
            isset($appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT])
            && $appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT] != null &&
            $appointmentData->appointmentVia == AppointmentVia::SELF
        ) {
            $status = AppointmentUserStatusEnum::STATUS_MONITORING;
        } else {
            $status = AppointmentUserStatusEnum::STATUS_SUCCESSFUL;
        }
        // store appointment
        $appointmentUserModel = [
            'service_id' => $appointmentData->serviceId,
            'place_id' => $appointmentData->placeId,
            'user_id' => $userModelAppointment->userModel->user->id,
            'doctor_id' => $appointmentSetting->user_id,
            'agent_id' => $appointmentData->agentId,
            'operator_id' => $appointmentData->operatorId,
            'tracking_code' => AppointmentUser::generateTrackingCode(),
            'status' => $status,
            'kind' => $appointmentData->kind,
            'type' => $appointmentData->type,
            'start_time' => $visitDateTime->toTimeString(),
            'end_time' => $endTime,
            'date_visit' => $visitDateTime->toDateTimeString(),
            'user_ip' => ip(),
        ];
        if ($appointmentData->kind == AppointmentUserKindEnum::ONLINE) {
            $appointmentUserModel['start_time'] = null;
            $appointmentUserModel['end_time'] = null;
        }
        $detailDatabaseDB['payment'] = [
            'status' => false,
        ];



        //description for app
        if ($appointmentData->description) {
            $detailDatabaseDB[AppointmentUser::DETAIL_DESCRIPTION] =  $appointmentData->description;
        }

        // handel payment
        $paymentLink = null;
        $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
        if ($appointmentData->appointmentVia == AppointmentVia::SELF && $paymentstatus['status']) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
            $appointmentUserModel['deadline_at'] = $paymentstatus['deadline'];
            if ($paymentstatus['force_payment']) {
                $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            }
            $detailDatabaseDB[AppointmentUser::DETAIL_PAYMENT] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['price'],
            ];
        }

        // detailDatabase

        $detailDatabaseDB[AppointmentUser::DETAIL_FOR_HIMSELF] = $userModelAppointment->forHimself;
        if ($userModelAppointment->forHimself == 2) {
            $detailDatabaseDB[AppointmentUser::DETAIL_SOMEONE] = SomeoneResource::make($userModelAppointment->userSomeoneModel);
        }

        // store question in DB
        if (isset($detailAppointment[AppointmentUser::DETAIL_QUESTION])) {
            $detailDatabaseDB[AppointmentUser::DETAIL_QUESTION] = $detailAppointment[AppointmentUser::DETAIL_QUESTION];
        }

        $detailDatabaseDB[AppointmentUser::DETAIL_APPOINTMENT_VIA] = $appointmentData->appointmentVia;
        $appointmentUserModel['details'] = $detailDatabaseDB;

        // store appointment in DB
        $appointmentUser = $appointmentSetting->appointmentUsers()->create($appointmentUserModel);

        // insert online appointment
        if ($appointmentData->kind == AppointmentUserKindEnum::ONLINE) {
            $this->insertOnlineAppointment($appointmentUser);
        }

        // create payment link
        if ($appointmentData->appointmentVia == AppointmentVia::SELF && $paymentstatus['status']) {
            $paymentLink = route('appointmentUser.payment', $appointmentUser);
        }

        // handel sms
        $this->makeShortLink($appointmentUser);

        // send sms
        if (isset($smsTemplate)) {
            $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
        }
        event(new StoreAppointmentEvent($appointmentUser));

        return [
            'status' => true,
            'message' => 'نوبت با موفقیت برای کاربر ثبت شد',
            'detail' => [
                'tracking_code' => $appointmentUserModel['tracking_code'],
                'appointment_user_id' => $appointmentUser->id,
                'tracking_url' => $appointmentUser->shortLink()->first()->link_url,
                'payment_link' => $paymentLink
            ]
        ];

        //        $appointmentLists->where('start_time', '>', $dateAppointment)->where('end_time',);
    }
}
