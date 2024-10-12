<?php

namespace Modules\AppointmentUser\Service;

use App\Event;
use Carbon\Carbon;
use App\Enum\RouteEnum;
use App\Models\ShortLink;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Api\Transformers\UserResource;
use Modules\Api\app\Resources\PriceResource;
use Modules\Transaction\app\Models\Transaction;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Api\app\Resources\Api\SomeoneResource;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Events\StoreAppointmentEvent;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentDocAndOperatorNotification;

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

        if (array_key_exists('specialDay', $details)) {
            $specialDaySelected = true;
            $startDate = Carbon::parse($details['specialDay'])->subDays(20);
            $endDate = $startDate->copy()->addDays(20)->addDays($details['specialDay_endDate'] ?? 60);
        } elseif (array_key_exists('specialDays', $details)) {
            $startDate = Carbon::parse($details['specialDays'])->subDays(20);
            $endDate = $startDate->copy()->addDays(20)->addDays(90);
        } elseif (array_key_exists('completeDays', $details)) {
            $startDate = Carbon::parse($details['specialDay']);
            $endDate = $startDate->copy()->addDays($details['numberDays'] ?? 90);
        }

        if (!$specialDaySelected && !isset($startDate)) {
            $startDate = Carbon::today()->subDays(20);
            $endDate = Carbon::today()->addDays(90);
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
                'user_status' => true,
                'day_number' => verta($currentDate)->format("l m/d"),
                'day_number_gmt' => $currentDate->toDateString(),
                'is_holiday' => false,
                'empty_appoints' => 0,
                'times' => [],
            ];
            if ($appointmentSettings->count()) {
                // Get the time for each visit in minutes
                $timeForVisit = $appointmentSettings->time_for_visit;
                if (isset($details['segment_time'])) {
                    $timeForVisit = $details['segment_time'];
                }

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
                            'user_status' => false,
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
                        $dayOutput['user_status'] = false;
                        $dayOutput['empty_appoints'] = 0;
                        break;
                    }
                    // In case of non-attendance

                    // check holiday
                    if ($holidays->contains('date', $currentDate->toDateString())) {
                        $dayOutput['is_holiday'] = true;
                        $dayOutput['status'] = true;
                        $dayOutput['user_status'] = true;
                        $dayOutput['empty_appoints'] = 0;
                        if ($checkHoliday) {
                            $dayOutput['status'] = false;
                            $dayOutput['user_status'] = false;
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
                                $thisStatus = !$currentDate->copy()->addDay()->isPast();

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

                                    // In the event that the final hour is greater than the required time of the visit
                                    if ($timeForVisit > $getLastOverLapsTime['overLapTime']) {
                                        $startTime->subMinutes($timeForVisit - $getLastOverLapsTime['overLapTime']);
                                    }

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
                    $dayOutput['user_status'] = false;
                    $dayOutput['empty_appoints'] = 0;
                }

                // check for minimum start date from , for user
                if ($appointmentSetting->min_day_active > 0) {
                    if ($currentDate->lte(\now()->addDays($appointmentSetting->min_day_active))) {
                        $dayOutput['user_status'] = false;
                    }
                }

                // Fill the slots with appointments
            } else {
                $dayOutput['status'] = false; // Appointment settings not found for the doctor
                $dayOutput['user_status'] = false; // Appointment settings not found for the doctor
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
        //TODO::change this function for VOIP and inPerson Payment
        $deadLineDelete = null;
        $onlineStatusPayment = false;
        $onlineForcePayment = false;
        $onlinePrice = null;

        $detail = $appointmentSetting['detail'];
        if (isset($detail['payment']) && isset($detail['payment']['online']) && $detail['payment']['online']['status']) {
            $onlineStatusPayment = true;
            if (isset($detail['payment']['online']['notPayinStatus']) && $detail['payment']['online']['notPayinStatus'] == AppointmentSetting::DETAIL_PAYMENT_NOT_PAY_STATUS_DONT_SUBMIT) {
                $deadLineDelete = Carbon::now()->addHours(4)->toDateTimeString();
                $onlineForcePayment = true;
            }
            $onlinePrice = $detail['payment']['online']['price'];
        }
        $inPersonStatusPayment = false;
        $inPersonForcePayment = false;
        $inPersonPrice = null;
        if (isset($detail['payment']) && isset($detail['payment']['inPerson']) && $detail['payment']['inPerson']['status']) {
            $inPersonStatusPayment = true;
            if (isset($detail['payment']['online']['notPayinStatus']) && $detail['payment']['online']['notPayinStatus'] == AppointmentSetting::DETAIL_PAYMENT_NOT_PAY_STATUS_DONT_SUBMIT) {
                $deadLineDelete = Carbon::now()->addHours(4)->toDateTimeString();
                $inPersonForcePayment = true;
            }
            $inPersonPrice = $detail['payment']['inPerson']['price'];
        }
        return [
            'online' => [
                'status' => $onlineStatusPayment,
                'deadline' => $deadLineDelete,
                'force_payment' => $onlineForcePayment,
                'price' => $onlinePrice > 0 ? PriceResource::make(['price' => $onlinePrice]) : null,
            ],
            'in_person' => [
                'status' => $inPersonStatusPayment,
                'deadline' => $deadLineDelete,
                'force_payment' => $inPersonForcePayment,
                'price' => $inPersonPrice > 0 ? PriceResource::make(['price' => $inPersonPrice]) : null,
            ]

        ];
    }

    public function handleSms(AppointmentUser $appointmentUser) {}

    private function makeShortLink($appointmentUser)
    {
        $appointmentUser->shortLink()->create([
            'link_code' => ShortLink::generateShortLinkCode(),
            'link_url'  => route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code]),
        ]);
    }

    private function insertOnlineAppointment(AppointmentUser $appointmentUser): appointmentOnline
    {
        $status = $appointmentUser->status->convertToAppointmentOnlineStauts();
        $appointmentOnline =  $appointmentUser->online()->create([
            'appointment_setting_id' => $appointmentUser->setting->id,
            'user_id' => $appointmentUser->user->id,
            'doctor_id' => $appointmentUser->doctor->id,
            'tracking_code' => AppointmentOnline::generateTrackingCode(),
            'status' => $status,
            'date_visit' => $appointmentUser->date_visit,
        ]);
        return  $appointmentOnline;
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
            $dateAppointment = Carbon::createFromTimestamp($appointmentData->timestamp, 'Asia/Tehran');
            $visitDateTime = Carbon::createFromTimestamp($appointmentData->timestamp, 'Asia/Tehran');
        }

        if (
            $appointmentData->kind == AppointmentUserKindEnum::IN_PERSION
            && $appointmentData->appointmentVia == AppointmentVia::SELF && $dateAppointment->isPast()
        ) {
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

        // check if time is full
        $appoiutnemtTime = Carbon::createFromTimestamp($appointmentData->timestamp, 'Asia/Tehran')->toDateTimeString();
        $checkForAppointmentExists = AppointmentUser::where('appointment_setting_id', $appointmentSetting->id)
            ->whereIn('status', [
                AppointmentUserStatusEnum::STATUS_PENDING,
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                AppointmentUserStatusEnum::STATUS_ATTENDED,
                AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                AppointmentUserStatusEnum::STATUS_NOT_ATTENDED,
                AppointmentUserStatusEnum::STATUS_MONITORING,
            ])
            ->where('date_visit', $appoiutnemtTime)->exists();
        if ($checkForAppointmentExists) {
            return [
                'status' => false,
                'message' => 'ساعت انتخابی شما پر شده است، لطفا بازگردید و ساعت دیگری را انتخاب کنید',
                'route' => 'time'
            ];
        }


        $paymentstatus = $this->paymentstatus($appointmentSetting);
        // create payment link

        // check if end time has set by admin
        $endTime = $appointmentData->endTime ?? $visitDateTime->copy()->addMinutes($appointmentSetting->time_for_visit)->toTimeString();

        $status = AppointmentUserStatusEnum::STATUS_SUCCESSFUL;

        // check for peyment
        if (
            $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS]
            && $appointmentData->appointmentVia == AppointmentVia::SELF
        ) {
            if ($appointmentData->kind == AppointmentUserKindEnum::IN_PERSION) {
                if (
                    $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::IN_PERSON][AppointmentSetting::STATUS] == true
                ) {
                    $status = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
                }
            } elseif ($appointmentData->kind == AppointmentUserKindEnum::ONLINE) {
                if (
                    $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE][AppointmentSetting::STATUS] == true
                ) {
                    $status = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
                }
            } elseif ($appointmentData->kind == AppointmentUserKindEnum::VOIP) {
                if (
                    $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::VOIP][AppointmentSetting::STATUS] == true
                ) {
                    $status = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
                }
            }
        }

        //check for monitoring appointment
        if (
            isset($appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT]) &&
            $appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT] !== null &&
            $appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT] !== false &&
            $appointmentData->appointmentVia == AppointmentVia::SELF
        ) {
            $status = AppointmentUserStatusEnum::STATUS_MONITORING;
            $monitoring_deadline = Carbon::now()->addHours((int) $appointmentSetting->detail[AppointmentSetting::MONITORTING_APPOINTMENT]) ;
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
            // TODO::تایید نوبت در نوبت های آنلاین
            // if (
            //     $appointmentData->appointmentVia == AppointmentVia::SELF
            //     && $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE][AppointmentSetting::STATUS] != true
            // ) {
            //     $appointmentUserModel['status'] =  AppointmentUserStatusEnum::STATUS_PENDING;
            // }
        }
        $detailDatabaseDB['payment'] = [
            'status' => false,
        ];

        // if set the appointment to be WAIT_FOR_PAYMENT
        if (isset($detail['wait_for_payment'])) {
            $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            $hours =  setting(SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_ADMIN) == null ?   config('app.appointment_dedline') : setting(SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_ADMIN);
            $appointmentUserModel['deadline_at'] =  \now()->addHours((int)$hours);
        }

        //description for app
        if ($appointmentData->description) {
            $detailDatabaseDB[AppointmentUser::DETAIL_DESCRIPTION] =  $appointmentData->description;
        }

        // handel payment
        $paymentLink = null;
        $needToPayment = false;
        $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);

        if (
            $appointmentData->appointmentVia == AppointmentVia::SELF &&
            $appointmentData->kind == AppointmentUserKindEnum::ONLINE &&
            $paymentstatus['online']['status']
        ) {
            $needToPayment = true;
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
            $appointmentUserModel['deadline_at'] = $paymentstatus['online']['deadline'];
            if ($paymentstatus['online']['force_payment']) {
                $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            }
            $detailDatabaseDB[AppointmentUser::DETAIL_PAYMENT] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['online']['price'],
            ];
        }
        if (
            $appointmentData->appointmentVia == AppointmentVia::SELF &&
            $appointmentData->kind == AppointmentUserKindEnum::IN_PERSION &&
            $paymentstatus['in_person']['status']
        ) {
            $needToPayment = true;
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
            $appointmentUserModel['deadline_at'] = $paymentstatus['in_person']['deadline'];
            if ($paymentstatus['in_person']['force_payment']) {
                $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            }
            $detailDatabaseDB[AppointmentUser::DETAIL_PAYMENT] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['in_person']['price'],
            ];
        }
        // insert dead_line for monioring app
        if(isset($monitoring_deadline)) {
            $appointmentUserModel['deadline_at'] = $monitoring_deadline ;
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

        $detailDatabaseDB[AppointmentUser::STORE_FROM_APPLICATION] = false;
        if (isset($detailAppointment[AppointmentUser::STORE_FROM_APPLICATION])) {
            $detailDatabaseDB[AppointmentUser::STORE_FROM_APPLICATION] = $detailAppointment[AppointmentUser::STORE_FROM_APPLICATION];
        }

        $detailDatabaseDB[AppointmentUser::DETAIL_APPOINTMENT_VIA] = $appointmentData->appointmentVia;
        if (isset($detail['wait_for_payment'])) {
            $detailDatabaseDB[AppointmentUser::PENDING_APPOINTMENT_BY_SECRETERY] = true;
        }
        $appointmentUserModel['details'] = $detailDatabaseDB;

        // store appointment in DB
        $appointmentUser = $appointmentSetting->appointmentUsers()->create($appointmentUserModel);

        // insert online appointment
        if ($appointmentData->kind == AppointmentUserKindEnum::ONLINE) {
            $insertedOnlineAppointment =  $this->insertOnlineAppointment($appointmentUser);
        }

        // create payment link
        if (
            $appointmentData->appointmentVia == AppointmentVia::SELF &&
            $appointmentData->kind == AppointmentUserKindEnum::ONLINE &&
            $paymentstatus['online']['status']
        ) {
            $paymentLink = route('api.appointment.payment.create', $appointmentUser);
        } elseif (
            $appointmentData->appointmentVia == AppointmentVia::SELF &&
            $appointmentData->kind == AppointmentUserKindEnum::ONLINE
        ) {
            // // send online first message
            if (setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS)) {
                $insertedOnlineAppointment->messages()->create([
                    'user_id' => $insertedOnlineAppointment->user_id,
                    'answer_by' => 1,
                    'type' => AppointmentOnlineMessageTypeEnum::ANSWER,
                    'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
                    'body' => setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE) ?? 'سلام لطفا سوال خود را مطرح کنید',
                ]);
            }
        }
        if (
            $appointmentData->appointmentVia == AppointmentVia::SELF &&
            $appointmentData->kind == AppointmentUserKindEnum::IN_PERSION &&
            $paymentstatus['in_person']['status']
        ) {
            $paymentLink = route('api.appointment.payment.create', $appointmentUser);
        }
        if ($status == AppointmentUserStatusEnum::STATUS_MONITORING) {
            $smsTemplate = setting(SettingKeyEnum::SMS_SET_APP_MONITORING);
        }
        // handel sms
        $this->makeShortLink($appointmentUser);

        if (!$needToPayment) {
            if (isset($detail['smsTemplate'])) {
                $smsTemplate = $detail['smsTemplate'];
            }
            // send sms
            if ($appointmentData->sendSmsToUser) {
                if (isset($smsTemplate)) {
                    $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
                }
            }
            if (isset($appointmentUser->operator)) {
                $smsToOperator = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_OPERATOR);
                if (isset($smsToOperator)) {
                    $appointmentUser->notify(new AppointmentDocAndOperatorNotification($smsToOperator, $appointmentUser->operator->mobile));
                }
            }
            if (isset($appointmentUser->doctor)) {
                $smsToDoctor = setting(SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR);
                if (isset($smsToDoctor)) {
                    $appointmentUser->notify(new AppointmentDocAndOperatorNotification($smsToDoctor, $appointmentUser->doctor->mobile));
                }
            }
        } else {
            $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
        }

        $transactionId = null;
        // Create transaction when payment is inactive
        if ($paymentLink === null) {
            $transactionData = [
                'user_id' => $userModelAppointment->userModel->user->id,
                'transaction_code' => Transaction::generateTransactionCode(),
                'status' => TransactionStatusEnum::SUCCESSFUL,
                'cost' => 0,
                'total_cost' => 0,
                'paid_by' => TransactionPaidEnum::NO_NEED_TO_PAY,
            ];
            $transaction = $appointmentUser->transaction()->updateOrCreate($transactionData);
            $transactionId = $transaction->id;
        }
        event(new StoreAppointmentEvent($appointmentUser));

        Cache::forget('appointmentList.' . $appointmentSetting->id);
        Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            $appointmentSetting->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
        $trackingUrl = route('front.setAppointment.detail', ['tracking_code' => $appointmentUser->tracking_code]);
        return [
            'status' => true,
            'message' => 'نوبت با موفقیت برای کاربر ثبت شد',
            'detail' => [
                'tracking_code' => $appointmentUserModel['tracking_code'],
                'appointment_user_id' => $appointmentUser->id,
                'tracking_url' => $trackingUrl,
                'transaction_id' => $transactionId,
                'route' => $transactionId ? RouteEnum::TRANSACTION->getLink($transactionId) : null,
                'payment_link' => $paymentLink ?? $trackingUrl
            ]
        ];

        //        $appointmentLists->where('start_time', '>', $dateAppointment)->where('end_time',);
    }
}
