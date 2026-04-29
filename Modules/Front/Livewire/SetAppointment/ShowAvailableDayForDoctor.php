<?php

namespace Modules\Front\Livewire\SetAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use function PHPUnit\Framework\isFalse;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;

use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;

#[Layout('front::layouts.app')]
#[Title('ثبت نوبت')]
class ShowAvailableDayForDoctor extends Component
{
    public $msg = false;
    public array $form = [];

    #[Locked]
    public array $fetchData = [];
    public ?AppointmentSetting $appointmentSetting;

    public function TimeForReservesation()
    {
        $this->validate(['form.time' => 'required|string']);
        // Extract start and end times from the input value
        list($startTime, $endTime) = explode(',', $this->form['time']);

        /////////////// startTime contain date and start time as a timestamp

        // Convert the times to endTime
        $eTime  = Carbon::createFromFormat('H:i', $endTime);
        $endTimestamp = $eTime->timestamp;
        // Create the parameter array with the converted timestamps
        $parameter = [
            'doctor_id' => $this->fetchData['doc']->id,
            'place_id'  => $this->fetchData['places']->id,
            'service_id' => $this->fetchData['service']->id,
            'start_time' => $startTime,
            'end_time' => $endTimestamp,
            'is_online' => 'false',
        ];
        if (isset($this->fetchData['segments'])) {
            $segId = [];
            foreach ($this->fetchData['segments'] as $seg) {
                $segId[] = $seg->id;
            }
            $parameter['segmentId'] = implode(',', $segId);
        }
        if (isset($this->fetchData['selected_operator'])) {
            $parameter['operator'] = $this->fetchData['selected_operator'];
        }
        return $this->redirect(route('setAppointment.checkout', $parameter), true);
    }
    public function loadMoreDays()
    {
        $last_day_active = $this->fetchData['lastDate'];
        // //  check if user can access this date of appointments
        $max_days_app_available = $this->maxAvailableAppointmentDate();
        if ($max_days_app_available) {
            if ($max_days_app_available->lte($this->fetchData['lastDate'])) {
                $this->dispatch('scrollToBottom', true);
                return $this->msg = 'بازه ی نمایش به اتمام رسیده است!';
            }
        }
        $this->fetchData['maxShowDay'] = $this->fetchData['maxShowDay'] + 2;

        // check if date exist in the log or should recreate the app_log
        if (isset($this->fetchData['lastDate']) && isset($this->fetchData['last_active_day']) && $this->fetchData['lastDate']->lt($this->fetchData['last_active_day'])) {
            // next date exist in log
            $this->fetchData['firstTreeAvailableAppointment'] =  $this->findFirstTreeAppointment($this->fetchData['rawlistOfAppointment'], $this->fetchData['lastDate']);
            if ($last_day_active == $this->fetchData['lastDate']) {
                // if last day active remain same as was before , max_days_app_available is grater that $fetchData['lastDate'] but there is no day to show so you should check if last day active changed or not
                $this->dispatch('scrollToBottom', true);
                return $this->msg = 'بازه ی نمایش به اتمام رسیده است!';
            } else {
                $this->dispatch('scrollToBottom', true);
            }
        } else {
            if (isset($this->fetchData['segment_time'])) {
                $details['segment_time'] =  $this->fetchData['segment_time'];
            }
            // next date is not exist in log
            $details['specialDays'] = $this->fetchData['lastDate'];
            $this->fetchData['rawlistOfAppointment'] =  app('AppointmentUserService')->listAppointments($this->fetchData['appointmentSetting'], $details);
            $this->fetchData['firstTreeAvailableAppointment'] = $this->findFirstTreeAppointment($this->fetchData['rawlistOfAppointment']);
            $this->dispatch('scrollToBottom', true);
        }
    }

    private function caculateLastActiveDay($listOfAppointment)
    {
        $last_exist_month = end($listOfAppointment);
        $list_of_last_mount_days = end($last_exist_month);
        $lastActiveDayGmt = null;
        foreach (array_reverse($list_of_last_mount_days) as $eachDayOfLastMounth) {
            if ($eachDayOfLastMounth['status'] === true) {
                $this->fetchData['last_active_day'] = Carbon::parse($eachDayOfLastMounth['day_number_gmt']);
                break;
            }
        }
    }

    private function maxAvailableAppointmentDate(): ?Carbon
    {
        $appointmentSetting = $this->fetchData['appointmentSetting'] ?? null;
        if (! isset($appointmentSetting->max_day_active)) {
            return null;
        }

        $now = Carbon::now('Asia/Tehran');
        $maxAvailableDate = $now->copy()->startOfDay()->addDays((int) $appointmentSetting->max_day_active);
        $openTime = data_get($appointmentSetting->detail, AppointmentSetting::OPEN_TIME,'00:00');

        if (! empty($openTime)) {
            try {
                $openAt = $now->copy()->setTimeFromTimeString($openTime);
                if ($now->lt($openAt)) {
                    $maxAvailableDate->subDay();
                }
            } catch (\Throwable $exception) {
                // Invalid stored time should not block appointments; fall back to the old midnight behavior.
            }
        }

        return $maxAvailableDate;
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
        $maxDay = $this->fetchData['maxShowDay'];
        $DaysDisplayed = 0;

        // select the last active day
        $this->caculateLastActiveDay($listOfAppointment['data']);

        foreach ($listOfAppointment['data'] as $yeay => $monthWithAppointment) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($monthWithAppointment as $month => $appointments) {

                foreach ($appointments as $day => $appointment) {
                    if ((Carbon::parse($appointment['day_number_gmt'])->setTime(00, 00, 00)->lt(Carbon::now()->addDays($mainDaActive)->setTime(00, 00, 00)))) {
                        // check min day active
                        continue;
                    }
                    if ((Carbon::parse($appointment['day_number_gmt'])->lt(Carbon::now()->today()))) {
                        continue;
                    }
                    if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
                        continue;
                    }
                    if (setting(SettingKeyEnum::APPOINTMENT_SHOW_FALSE_STATUS_DAYS) && setting(SettingKeyEnum::APPOINTMENT_SHOW_FALSE_STATUS_DAYS) != false) {
                        if ($appointment['status'] == false  &&  empty($appointment['times'])) {
                            continue;
                        }
                    } else {
                        if ($appointment['empty_appoints'] <= 0 || $appointment['status'] == false ||   $appointment['user_status'] == false) {
                            continue;
                        }
                    }
                    // check firstDay active in setting
                    if ($listOfAppointment['report']['first_day_active'] != null) {
                        $firstDayActive = Carbon::parse($listOfAppointment['report']['first_day_active']);
                        if ($firstDayActive->gt(Carbon::parse($appointment['day_number_gmt']))) {
                            continue;
                        }
                    }
                    $dayNumber = $appointment['day_number_gmt'];
                    // check if user can access this date "max_day_active" from "setting"
                    $last_activeDay = $this->maxAvailableAppointmentDate();
                    $date_to_check = carbon::parse($dayNumber);
                    if ($last_activeDay && $date_to_check->gt($last_activeDay)) {
                        break 3;
                    }
                    if ($DaysDisplayed > $maxDay) {
                        break 3;
                    }
                    $DaysDisplayed++;
                    foreach ($appointment['times'] as $increment =>  $time) {
                        $checkIfTimePass = false;
                        if ($time['status']) {
                            $timeToCheck = Carbon::CreateFromTimeStamp($time['timestamp'], 'Asia/Tehran');
                            $isToday = $timeToCheck->copy()->isToday();
                            if ($isToday) {
                                $checkIfTimePass = $timeToCheck->copy()->gt(now()->addHours(3));
                            } else {
                                $checkIfTimePass = $timeToCheck->isFuture();
                            }
                        }
                        if ($time['status'] && $checkIfTimePass) {
                            $result[$dayNumber][] = [
                                'status' => true,
                                'day_of_week_name'    =>  verta()->formatDifference(),
                                'day_name'            =>  verta($dayNumber)->format('l'),
                                'date_of_month'       =>  verta($dayNumber)->format('%d %B'),
                                'time_stamp' => Carbon::parse($dayNumber)->setTimeFromTimeString($time['from'])->timestamp,
                                'from' => substr($time['from'], 0, 5),
                                'until' => substr($time['until'], 0, 5),
                            ];
                        } else {
                            if (setting(\Modules\Setting\Enum\SettingKeyEnum::SHOW_FALSE_APPOINTMENT_STATUS)) {
                                $result[$dayNumber][] = [
                                    'status' => false,
                                    'from' => $time['from'],
                                ];
                            }
                        }
                    }
                    // delete the day if all the status are false
                    // $checkForFalse = collect($result[$dayNumber]);
                    // $isStatusFalse = $checkForFalse->every(function ($appointment) {
                    //     return $appointment['status'] == false;
                    // });
                    // if ($isStatusFalse) {
                    //     unset($result[$dayNumber]);
                    //     $DaysDisplayed = $DaysDisplayed - 1;
                    // }
                }
            }
        }
        // dd($result);
        $dates = array_keys($result);
        // Get the last date
        $this->fetchData['lastDate'] = Carbon::parse(end($dates));
        return $result;
    }

    private function getAvailableDay()
    {
        $appointmentSetting = AppointmentSetting::activeSetting()->where('service_id', $this->fetchData['service']->id)
            ->where('place_id', $this->fetchData['places']->id)
            ->where('user_id', $this->fetchData['doc']->id)
            ->first();
        //check for general setting
        if (!isset($appointmentSetting)) {
            $appointmentSetting = AppointmentSetting::activeSetting()->where('user_id', $this->fetchData['doc']->id)->first();
        }
        if (isset($this->fetchData['segment_time'])) {
            // if segment exists , genereate list of appointment
            $details = [];
            $details['segment_time'] =  $this->fetchData['segment_time'];
            $listOfAppointment =  app('AppointmentUserService')->listAppointments($appointmentSetting, $details);
        } else {
            if (env('APPOINTMENT_SANDBOX')) {
                Cache::forget('appointmentList.' . $appointmentSetting->id);
            }
            // if segmen whouldnt exists , load the days from log
            if (app()->environment('local')) {
                $appointmentSetting->update(['updated_log_at' => \now()]);
                $listOfAppointment = app('AppointmentUserService')->listAppointments($appointmentSetting);
            } else {
                if (config('app.without_cache')) {
                    $listOfAppointment = app('AppointmentUserService')->listAppointments($appointmentSetting);
                } else {
                    $listOfAppointment = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
                        $appointmentSetting->update(['updated_log_at' => \now()]);
                        return  app('AppointmentUserService')->listAppointments($appointmentSetting);
                    });
                }
            }
        }
        $this->fetchData['appointmentSetting'] = $appointmentSetting;
        $this->fetchData['rawlistOfAppointment'] = $listOfAppointment;
        $this->fetchData['firstTreeAvailableAppointment'] =  $this->findFirstTreeAppointment($listOfAppointment);
    }
    public function mount()
    {
        $doc     = filter_var(request()->get('doctor_id', null), FILTER_SANITIZE_NUMBER_INT);
        $place   = filter_var(request()->get('place_id', null), FILTER_SANITIZE_NUMBER_INT);
        $service = filter_var(request()->get('service_id', null), FILTER_SANITIZE_NUMBER_INT);
        $segments =  request()->get('segment', null);
        $operators =  trim(request()->get('operator', null));
        if (!isset($doc) || !isset($place) || !isset($service)) {
            return abort(404);
        }
        if (! is_null($segments)) {
            $route_segments = filter_var_array($segments, FILTER_SANITIZE_NUMBER_INT);
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

        if (! is_null($operators) && ! empty($operators)) {
            $this->fetchData['selected_operator'] = User::findOrFail((int) $operators);
            if (! $this->fetchData['selected_operator']->isOperator()) {
                abort(404);
            }
        }

        $this->fetchData['doc']      =   User::findOrFail($doc);

        $this->fetchData['places']   =   place::findOrFail($place);
        $this->fetchData['service']  =   Service::findOrFail($service);
        if (
            !$this->fetchData['places']->users()->where('user_id', $this->fetchData['doc']->id)->exists() ||
            !$this->fetchData['service']->user()->where('user_id', $this->fetchData['doc']->id)->exists()
        ) {
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
        $this->fetchData['maxShowDay'] = 2;

        //check if doctor has active appointmentsetting
        if (! AppointmentSetting::activeSetting()
            ->where('user_id', $this->fetchData['doc']->id)
            ->exists()) {
            return redirect()->route('front.doctor.profile', ['doctor_id' => $this->fetchData['doc']->id, 'doctor_name' => str_replace(' ', '_', $this->fetchData['doc']->full_name)]);
        }
        $this->getAvailableDay();
        $this->fetchData['isAppointmentActive'] = $this->fetchData['doc']->isDoctorActive();
        if (setting(SettingKeyEnum::APPOINTMENT_STATUS) != true) {
            $this->fetchData['isAppointmentActive'] = false;
        }

        //select the nearest appointment
        foreach ($this->fetchData['firstTreeAvailableAppointment']  as $date => $appointmentsWithDaysIndex) {
            foreach ($appointmentsWithDaysIndex as $eachTime => $appointmentDetail) {
                if ($appointmentDetail['status'] == false) {
                    continue;
                }
                $this->form['time'] = (string) $appointmentDetail['time_stamp'] . ',' . $appointmentDetail['until'];
                break 2; // Break out of both foreach loops

            }
        }
    }
    public function booted()
    {
        // clear alert mesage
        if ($this->msg != false) {
            $this->msg = false;
        }
    }
    public function render()
    {
        return view('front::livewire.set-appointment.show-available-day-for-doctor');
    }
}
