<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Traits\OprationButtonsTrait;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal\SpecificDayAppointmentRegistrationModal;

class SpecificDayAvailableAppointment extends Component
{
    use OprationButtonsTrait;
    //this propery shouldNOT exist in the final product
    public $tempMessage = null;
    public array $fetchData = ['showRegisterModal' => 'false'];
    public array $form = [];
    public array $edited = ['status' => false];
    public function updated($property)
    {
        if ($property == 'form.changeDate') {
            return $this->loadDifferentDayDetail();
        }
    }
    public function dateHasBeenChange()
    {
        $this->dispatch('dateHasBeenChange', newDate: verta($this->fetchData['selectedDate'])->format('Y-m-d'))->to(SpecificDayAppointmentRegistrationModal::class);
        $this->dispatch('urlDateChange', newDate: verta($this->fetchData['selectedDate'])->format('Y-m-d'));
    }
    public function loadDifferentDayDetail()
    {
        // if date has been change , this functio would be call
        $this->fetchData['selectedDate']      =  Verta::parse($this->form['changeDate'])->tocarbon();
        $start_date_in_list = carbon::parse($this->fetchData['listOfAppointment'][0]['date']);
        $end_date_in_list = carbon::parse($this->fetchData['listOfAppointment'][count($this->fetchData['listOfAppointment']) - 1]['date']);
        if ($this->fetchData['selectedDate']->gt($start_date_in_list) && $this->fetchData['selectedDate']->lte($end_date_in_list)) {
        } else {
            $this->RecreatelistOfAppointment();
        }
        // array_column($this->fetchData['listOfAppointment'] , 'date')
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function previousDay()
    {
        if (isset($this->fetchData['showingAppointmentIndex']) && !array_key_exists(($this->fetchData['showingAppointmentIndex'] - 1), $this->fetchData['listOfAppointment'])) {
            $this->RecreatelistOfAppointment();
        } else {
            $privous_array = $this->fetchData['listOfAppointment'][($this->fetchData['showingAppointmentIndex'] - 1)];
            $privous_array_date = Carbon::parse($privous_array['date']);
            $this->fetchData['selectedDate'] = $privous_array_date;
        }
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function nextDay()
    {
        if (isset($this->fetchData['showingAppointmentIndex']) && !array_key_exists(($this->fetchData['showingAppointmentIndex'] + 1), $this->fetchData['listOfAppointment'])) {
            $this->RecreatelistOfAppointment();
        } else {
            $next_array = $this->fetchData['listOfAppointment'][($this->fetchData['showingAppointmentIndex'] + 1)];
            $next_array_array_date = Carbon::parse($next_array['date']);
            $this->fetchData['selectedDate'] = $next_array_array_date;
        }
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function RecreatelistOfAppointment()
    {
        $app = $this->fetchData['appointmentSetting'];
        $newListTimes = app('AppointmentUserService')
            ->listAppointments($app, ['specialDays' => $this->fetchData['selectedDate']->toDateString()]);
        $this->fetchData['listOfAppointment'] =  $this->listOfAppointment($newListTimes);
        foreach ($this->fetchData['listOfAppointment'] as $index => $avaiableTimes) {
            if ($avaiableTimes['date'] == $this->fetchData['selectedDate']->format('Y-m-d')) {
                $this->fetchData['showingAppointmentIndex'] = $index;
            }
        }
    }
    #[Computed]
    public function ShowListOfAppointmentForSpecificDay()
    {
        foreach ($this->fetchData['listOfAppointment'] as $index => $avaiableTimes) {
            if ($avaiableTimes['date'] == $this->fetchData['selectedDate']->format('Y-m-d')) {
                $this->fetchData['showingAppointmentIndex'] = $index;
                return $avaiableTimes['times'];
            }
        }
        if (!isset($this->fetchData['showingAppointmentIndex'])) {
            $this->fetchData['showingAppointmentIndex'] = 5;
        }
        // when selected date is not exist in log date range
        // $app = $this->fetchData['appointmentSetting'];
        // $newListTimes = app('AppointmentUserService')->listAppointments($app, ['specialDay' => $this->fetchData['selectedDate']->toDateString()]);
        // return $this->listOfAppointment($newListTimes)[0]['times'];
    }
    private function listOfAppointment($listOfAppointment)
    {
        $firstTwoEmpty = [];
        $report = $listOfAppointment['report'];
        $mainDaActive = $report['min_day_active'];
        $isDay   = Carbon::now()->format('Y-m-d');
        $result = [];
        $temPResult = [];
        if (isset($listOfAppointment['data'])) {
            foreach ($listOfAppointment['data'] as $yeay => $day) {
                foreach ($day as $month => $appointments) {
                    foreach ($appointments as $day => $appointment) {
                        $dayNumber = $appointment['day_number_gmt'];
                        foreach ($appointment['times'] as $time) {
                            if ($time['status']) {
                                // Increment the counter
                                $temPResult[] = [
                                    'status' => true,
                                    'time_stamp' => $time['timestamp'],
                                    'from' => $time['from'],
                                    'until' => $time['until'],
                                    'gap' => isset($time['gap']) ? true : false,
                                ];
                                // If two matches are found, break out of the loop
                            } else {
                                $temPResult[] = [
                                    'status' => false,
                                    'from' => $time['from'],
                                    'until' => $time['until'],
                                    'appointment_user_id' => isset($time['appointment_user_id']) ? $time['appointment_user_id'] : null,
                                    'gap' => isset($time['gap']) ? true : false,
                                ];
                            }
                        }
                        if (empty($temPResult)) {
                            continue;
                        } else {
                            if (isset($temPResult)) {
                                $result[] = [
                                    'date' => $dayNumber,
                                    'times' => $temPResult,
                                    'is_active' => $isDay == $dayNumber,
                                ];
                            }
                        }
                        unset($temPResult);
                    }
                }
            }
        }
        return $result;
    }

    #[On('docHasChange')]
    public function RebiuldCacheDataWithDoctorId($appId)
    {
        $app = AppointmentSetting::find($appId);
        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $app->id);
        }
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            $app->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($app);
        });
        // dd($this->fetchData['RawlistOfAppointment']);
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);
    }
    public function passTimeToRegisterAppointmentModal($from, $until)
    {
        $this->dateHasBeenChange();
        $this->dispatch('time', from: $from, until: $until);
        $this->dispatch('lunchRegisterModal', true);
    }
    public function lunchAppModal()
    {
        $this->fetchData['showRegisterModal'] = 'true';
    }
    public function changeAppointmentType($appId)
    {
        $appUser = AppointmentUser::find($appId);
        $appUser->update([
            'type' => AppointmentUserTypeEnum::BETWEEN_PATIENTS,
        ]);
        Cache::forget('appointmentList.' . $this->fetchData['appId']);
        return redirect()->route(
            'admin.appointment.add.specificday',
            [
                'serviceId' => $this->fetchData['service']->id,
                'placeId' => $this->fetchData['place'],
                'appId'         =>   $this->fetchData['appId'],
                'date'          => verta($this->fetchData['selectedDate'])->format('Y-m-d'),
            ]
        )->with('success', 'وضعیت نوبت با موفقیت تغییر پیدا کرد');
    }
    public function editAppointment($id)
    {
        $app = AppointmentUser::find($id);
        return redirect()->route(
            'admin.appointment.add.specificday',
            [
                'serviceId' => $this->fetchData['service']->id,
                'placeId' => $this->fetchData['place'],
                'appId'         =>   $this->fetchData['appId'],
                'date'          => verta($this->fetchData['selectedDate'])->format('Y-m-d'),
                'tracking_code' => $app->tracking_code
            ]
        );
    }
    // opration button functions
    #[On('confirm_swal')]
    public function swal_confirm($action, $model)
    {
        return match ($action) {
            'GroupCancel'      => $this->cancelSelectedApp(),
            'changeType'       => $this->changeType($model),
            'cancelWithSms'    => $this->cancelAppointment($model, true),
            'cancelWithOutSms' => $this->cancelAppointment($model, false),
            'delete'           => $this->cancelAndDeleteApp($model),
            default => '',
        };
    }

    //opration button functions
    private function redirectToPage($msg)
    {
        return redirect()->route('admin.appointment.add.specificday', ['serviceId' => $this->fetchData['service']->id, 'placeId' => $this->fetchData['place'], 'appId' => $this->fetchData['appId'],  'date' => verta($this->fetchData['selectedDate'])->format('Y-m-d')])->with('success', $msg);
    }
    // opration button functions

    // when tracking_code is exist in url
    public function changeAppointmentDate($from, $until)
    {
        // Update the date
        $updateData = [
            'date_visit' => $this->fetchData['selectedDate']->todatetimestring(),
            'start_time' => $from,
            'end_time' => $until,
            'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
        ];

        // Check if the type needs to be updated
        if ($this->edited['old_app']->type == AppointmentUserTypeEnum::BETWEEN_PATIENTS) {
            $updateData['type'] = AppointmentUserTypeEnum::MAIN__APPOINTMENT;
        }

        // Update the appointment
        $this->edited['old_app']->update($updateData);
        $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE);
        if (isset($smsTemplate)) {
            $this->edited['old_app']->notify(new AppointmentSmsNotification($smsTemplate));
        }
        return redirect()->route('admin.appointment.add.specificday', [
            'serviceId' => $this->fetchData['service']->id,
            'placeId' => $this->fetchData['place'],
            'appId' =>  $this->fetchData['appId'],
            'date' => verta($this->fetchData['selectedDate'])->format('Y-m-d')
        ])->with('success', 'نوبت با موفقیت تغییر کرد');
    }
    public function mount()
    {
        $app = AppointmentSetting::find(request()->route('appId'));
        $this->fetchData['service'] = Service::find(request()->route('serviceId'));
        $this->fetchData['place'] = request()->route('placeId');
        $this->fetchData['doc'] = $app->user;
        $this->fetchData['appId'] = $app->id;
        $this->fetchData['appointmentSetting'] = $app;

        if (!empty(request()->route('date'))) {
            $this->fetchData['selectedDate']  = Verta::parse(request()->route('date'))->toCarbon();
        } else {
            return redirect()->back()->with('error', 'لطفا مجدد تاریخ را انتخاب کنید');
        }
        if ((request()->has('time'))) {
            $this->fetchData['time'] =  Carbon::createFromTimestamp(request()->get('time'))->toTimeString();
            $this->lunchAppModal();
            //lunch modal
        } else {
            $this->fetchData['time'] = null;
        }

        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $app->id);
        }
        // create inital list aof appointment
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
             $app->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($app);
        });
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);

        // check if selected date not exist in the log
        if ($this->fetchData['selectedDate']->gt(\now()->addDays(60))) {
            $this->form['changeDate'] = verta($this->fetchData['selectedDate'])->format('Y-m-d') ;
            $this->loadDifferentDayDetail();
        }

        // if user want to change the date of specific apppointment
        if (request()->has('tracking_code')) {
            $this->edited['status'] = true;
            $this->edited['old_app'] = AppointmentUser::firstWhere('tracking_code', request()->get('tracking_code'));
        }

        if (request()->has('serviceId')) {
            $this->fetchData['service'] = Service::find(request()->get('serviceId'));
        }
        if (User::doctors()->count() >  1 || Service::count() > 1) {
            $this->fetchData['showChangeServiceBtn'] = true;
        } else {
            $this->fetchData['showChangeServiceBtn'] = false;
        }
    }
    public function render()
    {

        return view('appointmentuser::livewire.admin.add-appointment.specific-day-available-appointment');
    }
}
