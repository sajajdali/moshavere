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
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
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
        // -----------------------------
        // 1) Resolve date range (unchanged behavior)
        // -----------------------------
        $specialDaySelected = false;
        if (array_key_exists('specialDay', $details)) {
            $specialDaySelected = true;
            $startDate = Carbon::parse($details['specialDay']);
            $endDate   = $startDate->copy()->addDays($details['specialDay_endDate'] ?? 60); // = specialDay + (endDate|60)
        } elseif (array_key_exists('specialDays', $details)) {
            $startDate = Carbon::parse($details['specialDays']);
            $endDate   = $startDate->copy()->addDays(60); // = specialDays + 90
        } elseif (array_key_exists('completeDays', $details)) {
            // NOTE: preserved your original key usage exactly (specialDay) to avoid changing behavior
            $startDate = Carbon::parse($details['specialDay']);
            $endDate   = $startDate->copy()->addDays($details['numberDays'] ?? 60);
        }
        if (!$specialDaySelected && !isset($startDate)) {
            $startDate = Carbon::today();
            $endDate   = Carbon::today()->addDays(60);
        }
        // -----------------------------
        // 2) Bulk-load everything we’ll need (no N+1)
        // -----------------------------
        $doctorId          = $appointmentSetting->user->id;
        $checkForInterface = $appointmentSetting->interference;

        // Holidays -> set for O(1) membership checks
        $holidays = Event::whereBetween('date', [$startDate, $endDate])
            ->where('is_holiday', '1')
            ->pluck('date')
            ->all();
        $holidaySet = array_fill_keys(array_map(fn($d) => (string)$d, $holidays), true);

        // Appointments -> pre-group by Y-m-d for O(1) day lookups
        $appointments = AppointmentUser::query()
            ->where('doctor_id', $doctorId)
            ->where('kind', AppointmentUserKindEnum::IN_PERSION)
            ->when($checkForInterface == false && $appointmentSetting->service_id != null, function ($q) use ($appointmentSetting) {
                return $q->where('service_id', $appointmentSetting->service_id);
            })
            ->whereBetween('date_visit', [$startDate, $endDate])
            ->orderBy('date_visit')
            ->orderBy('start_time')
            ->get(['id', 'date_visit', 'start_time', 'end_time', 'type', 'status']); // select only what we use

        $appointmentsByDay = [];
        foreach ($appointments as $a) {
            $key = Carbon::parse($a->date_visit)->toDateString();
            $appointmentsByDay[$key][] = $a;
        }

        // Attendance times (weekly/special) pre-indexed to avoid per-day filter()
        $appointmentSettings                     = $appointmentSetting;
        $appointmentSettingTimes                 = $appointmentSettings->times()->whereNull('special_date')->get();
        $appointmentSettingTimesHaveSpecialDays  = $appointmentSettings->times()->whereNotNull('special_date')->get();

        $weeklyTimesByDow   = []; // [0..6] => [times...]
        foreach ($appointmentSettingTimes as $t) {
            $weeklyTimesByDow[$t->day_number->value][] = $t;
        }
        $specialTimesByDate = []; // 'Y-m-d' => [times...]
        foreach ($appointmentSettingTimesHaveSpecialDays as $t) {
            $specialTimesByDate[(string)$t->special_date][] = $t;
        }

        // Absences -> one query; precompute covered dates for O(1) checks
        $serviceId = $appointmentSettings->service_id;
        $placeId   = $appointmentSettings->place_id;

        // dd($endDate,$startDate,$appointmentSetting->user->absence);
        $absenceQuery = $appointmentSetting->user->absence()
            ->whereDate('start_at', '<=', $endDate)
            ->whereDate('end_at', '>=', $startDate);

        if ($serviceId) {
            $absenceQuery->where(function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId)->orWhereNull('service_id');
            });
        }
        if ($placeId) {
            $absenceQuery->where(function ($q) use ($placeId) {
                $q->where('place_id', $placeId)->orWhereNull('place_id');
            });
        }

        $absences = $absenceQuery->get(['start_at', 'end_at']);
        $absenceDateSet = [];
        foreach ($absences as $abs) {
            $from = Carbon::parse($abs->start_at)->max($startDate)->startOfDay();
            $to   = Carbon::parse($abs->end_at)->min($endDate)->startOfDay();
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $absenceDateSet[$d->toDateString()] = true;
            }
        }

        // Max per day limit
        $maxAppointmentEachDay =
            (isset($appointmentSettings->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY])
                && (int)$appointmentSettings->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY] > 0)
            ? (int)$appointmentSettings->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY]
            : null;

        // -----------------------------
        // 3) Build output (same structure)
        // -----------------------------
        $output = [];
        $firstDayInLog = $firstEmptyDay = $lastDayInLog = null;

        // We’ll iterate with a clone to keep $startDate intact for report fields
        for ($currentDate = $startDate->copy(); $currentDate->lte($endDate); $currentDate->addDay()) {
            // Respect last_day_active (same logic)
            if ($appointmentSettings->last_day_active) {
                if (Carbon::parse($appointmentSettings->last_day_active)->lt($currentDate)) {
                    break;
                }
            }

            $numberAppointmentsPerDay = 0;
            $year  = verta($currentDate)->year;
            $month = verta($currentDate)->month;
            $day   = verta($currentDate)->day;

            $dayOutput = [
                'status'         => true,
                'user_status'    => true,
                'day_number'     => verta($currentDate)->format("l m/d"),
                'day_number_gmt' => $currentDate->toDateString(),
                'is_holiday'     => false,
                'empty_appoints' => 0,
                'times'          => [],
            ];

            if ($appointmentSettings) {
                // time_for_visit (segment override kept)
                $timeForVisit = $appointmentSettings->time_for_visit;
                if (isset($details['segment_time'])) {
                    $timeForVisit = $details['segment_time'];
                }
                $dayNumberFromEnum = AppintmentSettingDayNumber::getConstant(strToLower($currentDate->copy()->format('l')))->value;
                $customTimeVisit = $appointmentSettingTimes->firstWhere('day_number', $dayNumberFromEnum)?->time_for_visit;
                $timeForVisit = is_null($customTimeVisit) ? $timeForVisit : $customTimeVisit;
                // Pick attendance times for the day (special first, else weekly)
                $checkHoliday    = false;
                $attendanceTimes = [];
                $dateKey         = $currentDate->toDateString();

                if (!empty($specialTimesByDate[$dateKey])) {
                    $attendanceTimes = collect($specialTimesByDate[$dateKey]);
                } else {
                    $checkHoliday    = true;
                    $dow             = $currentDate->copy()->addDay()->dayOfWeek; // preserved your original +1 day logic
                    $attendanceTimes = collect($weeklyTimesByDow[$dow] ?? []);
                }
                // Place booked appointments (only for this day)
                if (!empty($appointmentsByDay[$dateKey])) {
                    foreach ($appointmentsByDay[$dateKey] as $appointment) {
                        $numberAppointmentsPerDay++;
                        $dayOutput['times'][] = [
                            'status'             => false,
                            'user_status'        => false,
                            'from'               => $appointment->start_time,
                            'type'               => $appointment->type->value,
                            'app_status'         => $appointment->status->value,
                            'until'              => $appointment->end_time,
                            'appointment_user_id' => $appointment->id,
                        ];
                    }
                }

                // sort by time_from before generating slots (same)
                usort($dayOutput['times'], function ($a, $b) {
                    return strtotime($a['from']) <=> strtotime($b['from']);
                });

                // Generate empty slots for each attendance time
                foreach ($attendanceTimes as $attendanceTime) {

                    // Absence check (precomputed)
                    if (isset($absenceDateSet[$dateKey])) {
                        $dayOutput['absence']      = true;
                        $dayOutput['status']       = false;
                        $dayOutput['user_status']  = false;
                        $dayOutput['empty_appoints'] = 0;
                        break;
                    }

                    // Holiday check (same behavior)
                    if (isset($holidaySet[$dateKey])) {
                        $dayOutput['is_holiday']   = true;
                        $dayOutput['status']       = true;
                        $dayOutput['user_status']  = true;
                        $dayOutput['empty_appoints'] = 0;
                        if ($checkHoliday) {
                            $dayOutput['status']      = false;
                            $dayOutput['user_status'] = false;
                            break;
                        }
                    }

                    if ($firstDayInLog === null) {
                        $firstDayInLog = $currentDate->copy();
                    }

                    $startTime = Carbon::parse($attendanceTime->start_at);
                    $endTime   = Carbon::parse($attendanceTime->end_at);


                    // Slot generation loop (unchanged logic)
                    while ($startTime->lt($endTime)) {

                        $overlaps = $this->isTimeRangeAvailable(
                            $startTime->toTimeString(),
                            $startTime->copy()->addMinutes($timeForVisit),
                            $dayOutput['times']
                        );

                        if ($overlaps['status'] == false) {
                            $until = $startTime->copy()->addMinutes($timeForVisit);

                            // handle end time visit
                            if ($endTime->lt($until)) {
                                $dayOutput['times'][] = [
                                    'status' => false,
                                    'from'   => $startTime->toTimeString(),
                                    'until'  => $endTime->toTimeString(),
                                    'gap'    => true,
                                ];
                            } else {
                                $thisStatus = !$currentDate->copy()->addDay()->isPast();

                                // max appointments/day
                                if ($maxAppointmentEachDay !== null && $maxAppointmentEachDay > 0) {
                                    if ($numberAppointmentsPerDay == $maxAppointmentEachDay) {
                                        $thisStatus = false;
                                    }
                                }

                                $dayOutput['times'][] = [
                                    'status'    => $thisStatus,
                                    'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                    'from'      => $startTime->toTimeString(),
                                    'until'     => $until->toTimeString(),
                                ];

                                if ($thisStatus) {
                                    $dayOutput['empty_appoints']++;
                                }

                                // preserve original stepping logic
                                $startTime = $until->subMinutes($timeForVisit);

                                // first empty day
                                if (!$firstEmptyDay && !$currentDate->isPast()) {
                                    $firstEmptyDay = [
                                        'day'       => verta($currentDate)->format("Y-m-d"),
                                        'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                        'from'      => $startTime->copy()->toTimeString(),
                                        'until'     => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                    ];
                                }
                            }
                        } else {
                            if ($overlaps['overLapTime'] != 0 && $overlaps['overLapTime'] < $timeForVisit) {

                                $startTime->addMinutes($overlaps['overLapTime']);
                                $overlapsAgain = $this->isTimeRangeAvailable(
                                    $startTime->toTimeString(),
                                    $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                    $dayOutput['times']
                                );

                                if ($overlapsAgain['status'] == false) {
                                    $until = $startTime->copy()->addMinutes($timeForVisit);

                                    if ($endTime->lt($until)) {
                                        $dayOutput['times'][] = [
                                            'status' => false,
                                            'from'   => $startTime->copy()->toTimeString(),
                                            'until'  => $endTime->toTimeString(),
                                            'gap'    => true,
                                        ];
                                    } else {
                                        $dayOutput['times'][] = [
                                            'status'    => !$currentDate->isPast(),
                                            'timestamp' => $startDate->copy()->timestamp, // preserved
                                            'from'      => $startTime->copy()->toTimeString(),
                                            'until'     => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                        ];
                                        $dayOutput['empty_appoints']++;

                                        if (!$firstEmptyDay && !$currentDate->isPast()) {
                                            $firstEmptyDay = [
                                                'timestamp' => $currentDate->copy()->setTime($startTime->hour, $startTime->minute)->timestamp,
                                                'day'       => verta($currentDate)->format("Y-m-d"),
                                                'from'      => $startTime->copy()->toTimeString(),
                                                'until'     => $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                            ];
                                        }
                                    }
                                } else {
                                    $startTime->subMinutes($overlaps['overLapTime']);
                                    $getLastOverLapsTime = $this->isTimeRangeAvailable(
                                        $startTime->toTimeString(),
                                        $startTime->copy()->addMinutes($timeForVisit)->toTimeString(),
                                        $dayOutput['times']
                                    );

                                    $startTimeOverLap = $getLastOverLapsTime['existingUntil'] == $overlapsAgain['existingUntil']
                                        ? $startTime->copy()->toTimeString()
                                        : $overlaps['existingUntil'];

                                    if ($timeForVisit > $getLastOverLapsTime['overLapTime']) {
                                        $startTime->subMinutes($timeForVisit - $getLastOverLapsTime['overLapTime']);
                                    }
                                    if ($startTimeOverLap != $overlapsAgain['existingFrom']) {
                                        if (
                                            !$this->hasExactBooked(
                                                $dayOutput['times'],
                                                $startTimeOverLap,
                                                $overlapsAgain['existingFrom']
                                            )
                                            && $startTimeOverLap !== $overlapsAgain['existingFrom']
                                        ) {
                                        } else {
                                            // مدت زمان ویزیت کمتر از زمان ویزیت میباشد را نمایشد نمیدهد
                                            $dayOutput['times'][] = [
                                                'status' => false,
                                                'from'   => $startTimeOverLap,
                                                'until'  => $overlapsAgain['existingFrom'],
                                                'gap'    => true,
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $startTime->addMinutes($timeForVisit);
                    }
                }

                if ($dayOutput['empty_appoints'] == 0) {
                    $dayOutput['status']      = false;
                    $dayOutput['user_status'] = false;
                    $dayOutput['empty_appoints'] = 0;
                }

                // min_day_active for users (same)
                if ($appointmentSetting->min_day_active > 0) {
                    if ($currentDate->lte(now()->addDays($appointmentSetting->min_day_active))) {
                        $dayOutput['user_status'] = false;
                    }
                }
            } else {
                $dayOutput['status']      = false;
                $dayOutput['user_status'] = false;
            }

            // final sort (same)
            usort($dayOutput['times'], function ($a, $b) {
                return strtotime($a['from']) <=> strtotime($b['from']);
            });

            $output['data'][$year][$month][$day] = $dayOutput;
            $lastDayInLog = $currentDate->toDateString();

            if ($specialDaySelected) {
                break;
            }
        }

        // -----------------------------
        // 4) Report block (same fields/values)
        // -----------------------------
        $paymentStatus   = false;
        $notPayinStatus  = $paymentPrice = $paymentOnline = $paymentVoip = null;
        if (isset($details['payment']['price']) && $details['payment']['price'] > 0) {
            $paymentStatus  = true;
            $paymentPrice   = $details['payment']['price'] ?? null;
            $paymentOnline  = $details['payment']['online']['status'] ?? null;
            $paymentVoip    = $details['payment']['voip']['status'] ?? null;
            $notPayinStatus = $details['payment']['online']['notPayinStatus'] ?? null;
        }

        $output['report'] = [
            'time_for_visit'   => $appointmentSettings->time_for_visit,
            'payment'          => [
                'status'         => $paymentStatus,
                'price'          => $paymentPrice,
                'paymentOnline'  => $paymentOnline,
                'paymentVoip'    => $paymentVoip,
                'notPayinStatus' => $notPayinStatus,
            ],
            'active_inPerson'  => $appointmentSettings['detail']['visit_type_inPerson'] ?? false,
            'active_voip'      => $appointmentSettings['detail']['visit_type_voip'] ?? false,
            'active_online'    => $appointmentSettings['detail']['visit_type_online'] ?? false,
            'last_day'         => isset($currentDate) ? $currentDate->toDateString() : null,
            'min_day_active'   => $appointmentSettings?->min_day_active,
            'first_empty_dayee' => $firstEmptyDay,
            'last_day_active'  => isset($appointmentSettings->last_day_active) ? $appointmentSettings->last_day_active->toDateString() : null,
            'last_day_in_log'  => $lastDayInLog,
            'first_day_in_log' => $firstDayInLog?->toDateString(),
            'first_day_active' => $appointmentSettings->first_day_active,
            'interference'     => $appointmentSettings->interference == 1,
        ];
        return $output;
    }
    private function hasExactBooked(array $times, string $from, string $until): bool
    {
        foreach ($times as $t) {
            if (
                isset($t['appointment_user_id']) &&
                ($t['from'] ?? null) === $from &&
                ($t['until'] ?? null) === $until
            ) {
                return true;
            }
        }
        return false;
    }
    public function isAppointmentTimeAvailable($startDateTime, $endDateTime, $dateVisit, AppointmentSetting $appointmentSetting)
    {
        // Check if there are any overlapping appointments
        $existingAppointments = AppointmentUser::where('doctor_id', $appointmentSetting->user_id)
            ->where('type', AppointmentUserTypeEnum::MAIN__APPOINTMENT)
            ->whereIn('status', AppointmentUserStatusEnum::confirmed());
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
                $deadLineDelete = Carbon::now()->addMinutes(30)->toDateTimeString();
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
        $isFromAdminPanell = isset($detail['store_from_admin_panel']) && $detail['store_from_admin_panel'] == true;
        if (! $isFromAdminPanell && $checkForAppointmentExists) {
            return [
                'status' => false,
                'message' => 'ساعت انتخابی شما پر شده است، لطفا بازگردید و ساعت دیگری را انتخاب کنید',
                'route' => 'time'
            ];
        }
        // check if selected time exists in setting
        if (
            ! $isFromAdminPanell &&
            $appointmentData->kind == AppointmentUserKindEnum::IN_PERSION &&
            $appointmentSetting->timeIsOutOfrange($appointmentData->timestamp)
        ) {
            return [
                'status' => false,
                'message' => 'ساعت انتخابی شما صحیح نیست ، لطفا بازگردید و یک ساعت دیگر انتخاب کنید',
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
            'start_time' => $visitDateTime->copy()->toTimeString(),
            'end_time' => $endTime,
            'date_visit' => $visitDateTime->copy()->toDateTimeString(),
            'user_ip' => ip(),
        ];
        if ($status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT) {
            $appointmentUserModel['deadline_at'] =  $this->getDeadlinePayment();
        }
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
            $appointmentUserModel['deadline_at'] =  $this->getDeadlinePayment();
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
            if ($paymentstatus['in_person']['force_payment']) {
                $appointmentUserModel['status'] = AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
            }
            $detailDatabaseDB[AppointmentUser::DETAIL_PAYMENT] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['in_person']['price'],
            ];
        }
        if (isset($detail['wait_for_payment'])) {
            // force payment for secretery send link appointments
            $detailDatabaseDB[AppointmentUser::DETAIL_PAYMENT] = [
                'status' => true,
                AppointmentUser::DETAIL_PAYMENT_PRICE => $paymentstatus['in_person']['price'],
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

        $detailDatabaseDB[AppointmentUser::STORE_FROM_APPLICATION] = false;
        if (isset($detailAppointment[AppointmentUser::STORE_FROM_APPLICATION])) {
            $detailDatabaseDB[AppointmentUser::STORE_FROM_APPLICATION] = $detailAppointment[AppointmentUser::STORE_FROM_APPLICATION];
        }

        $detailDatabaseDB[AppointmentUser::DETAIL_APPOINTMENT_VIA] = $appointmentData->appointmentVia;
        if (isset($detail['wait_for_payment'])) {
            $detailDatabaseDB[AppointmentUser::PENDING_APPOINTMENT_BY_SECRETERY] = true;
        }
        if (isset($detail['segments_ids'])) {
            $segmentItemId = explode(',', $detail['segments_ids']);
            $segments =  $appointmentSetting->segments
                ->first()
                ->items
                ->whereIn('id', $segmentItemId)
                ->map(function ($item) {
                    return [
                        'id'    => $item->id,
                        'title' => $item->title,
                        'time'  => $item->time,
                    ];
                })->values()->toArray();
            $detailDatabaseDB[AppointmentUser::DETAIL_SEGMENTS] = $segments;
            $segmentTotallTime = array_sum(array_column($segments, 'time'));
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
            if ($appointmentUser->status == AppointmentUserStatusEnum::STATUS_SUCCESSFUL && isset($appointmentUser->doctor)  && isset($appointmentData->smsToDoctor) && $appointmentData->smsToDoctor == true) {
                // check if sms to doctor is active
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
        if (! isset($segmentTotallTime)) {
            $segmentTotallTime = null;
        }
        // generate cache
        $appointmentUser->setting->runGenerateCacheJob($visitDateTime, $segmentTotallTime);

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

    private function getDeadlinePayment()
    {
        $hours = setting(SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_ADMIN) == null ?   config('app.appointment_dedline') : setting(SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_ADMIN);
        return   Carbon::now()->addHours((int)$hours)->toDateTimeString();
    }
}
