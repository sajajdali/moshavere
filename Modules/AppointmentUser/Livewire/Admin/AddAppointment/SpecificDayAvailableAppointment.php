<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Events\CancelAppointment;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Events\CancelAppointmentEvent;
use Modules\AppointmentUser\app\Events\DeleteAppointmentEvent;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal\SpecificDayAppointmentRegistrationModal;

class SpecificDayAvailableAppointment extends Component
{
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
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function previousDay()
    {
        $privous_array = $this->fetchData['listOfAppointment'][($this->fetchData['showingAppointmentIndex'] - 1)];
        $privous_array_date = Carbon::parse($privous_array['date']);
        $this->fetchData['selectedDate'] = $privous_array_date;
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function nextDay()
    {
        if(array_key_exists(($this->fetchData['showingAppointmentIndex'] + 1),$this->fetchData['listOfAppointment'])){
            //check if selected date exist in list of appointment
            $next_array = $this->fetchData['listOfAppointment'][($this->fetchData['showingAppointmentIndex'] + 1)];
        }else {
            // create new list of appointment based of selected date
             $this->RecreatelistOfAppointment();
        }
        $next_array_array_date = Carbon::parse($next_array['date']);
        $this->fetchData['selectedDate'] = $next_array_array_date;
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
    }
    public function RecreatelistOfAppointment() {
        $app = $this->fetchData['appointmentSetting'];
        $newListTimes = app('AppointmentUserService')
        ->listAppointments($app, ['specialDays' => $this->fetchData['selectedDate']->toDateString()]);
        dd($this->listOfAppointment($newListTimes)[0]);
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
        // when selected date is not exist in log date range
        $app = $this->fetchData['appointmentSetting'];
        $newListTimes = app('AppointmentUserService')->listAppointments($app, ['specialDay' => $this->fetchData['selectedDate']->toDateString()]);
        return $this->listOfAppointment($newListTimes)[0]['times'];
    }
    private function listOfAppointment($listOfAppointment)
    {
        $firstTwoEmpty = [];
        $report = $listOfAppointment['report'];
        $mainDaActive = $report['min_day_active'];
        $isDay   = Carbon::now()->format('Y-m-d');
        $result = [];
        $temPResult = [];
        if(isset($listOfAppointment['data'])) {
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

                        if (isset($temPResult)) {
                            $result[] = [
                                'date' => $dayNumber,
                                'times' => $temPResult,
                                'is_active' => $isDay == $dayNumber,
                            ];
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
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            return app('AppointmentUserService')->listAppointments($app);
        });
        // dd($this->fetchData['RawlistOfAppointment']);
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);;
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
    public function cancelSelectedApp()
    {
        foreach ($this->form['checkbox'] as $AppID => $checked) {
            if ($checked) {
                $app = AppointmentUser::find($AppID);
                $app->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]);
            }
        }
        event(new CancelAppointmentEvent($app));
        return  $this->redirectToPage('نوبت های انتخابی با موفقیت کنسل شدند');
    }
    public function changeType($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['type' =>  AppointmentUserTypeEnum::BETWEEN_PATIENTS]);
        return  $this->redirectToPage('نوبت به بین مریض تغییر پیدا کرد');
    }
    public function cancelAppointment($id, $sendSmsStatus)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' =>  AppointmentUserStatusEnum::STATUS_CANCEL]);
        if ($sendSmsStatus) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
            if (isset($smsTemplate)) {
                $app->notify(new AppointmentSmsNotification($smsTemplate));
            }
        }
        event(new CancelAppointmentEvent($app));
        return  $this->redirectToPage('نوبت با موفقیت کنسل شد');
    }
    public function cancelAndDeleteApp($id)
    {

        $this->cancelAppointment($id, true);
        $app = AppointmentUser::find($id);
        $app->delete();
        event(new CancelAppointmentEvent($app));
        event(new DeleteAppointmentEvent($app));
        $this->redirectToPage('نوبت با موفقیت حذف شد');
    }

    public function ApproveOnlineAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->id);
        $onlineApp?->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED]);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL]);
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApproveOnlineAppointment($id)
    {
        $this->fetchData['disapproveId'] = $id;
        $this->dispatch('lunchModal', true);
    }
    public function disaprovedModal()
    {
        $app = AppointmentUser::find($this->fetchData['disapproveId']);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        $detail = $app->details;
        if (isset($this->form['reason'])) {
            if (isset($detail)) {
                $detail = array_merge($detail, [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason']]);
            } else {
                $detail = [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason']];
            }
        }
        try {
            $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->id);
            $onlineApp->update(['status' => AppointmentOnlineStatusEnum::REJECT]);
            $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED, 'details' =>  $detail]);
        } catch (\Exception $th) {
            return redirect()->route('admin.appointment_user.list')->with('error', 'خطا در به روز رسانی');
        }
        $this->redirectToPage('وضعیت نوبت به عدم تایید ، تغییر پیدا کرد');
    }
    public function ApprovemonitoringAppointment($id)
    {

        $app = AppointmentUser::find($id);
        $deadLine_Time = $app->setting->detial[AppointmentSetting::MONITORTING_APPOINTMENT];
        $Appoointment_dedLine = now()->addHours($deadLine_Time);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT, 'deadline_at' => $Appoointment_dedLine]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT)));
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApprovemonitoringAppointment($id)
    {

        $app = AppointmentUser::find($id);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT)));
        $this->redirectToPage('نوبت با موفقیت لغو شد');
    }
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
        Cache::forget('appointmentList.' .   $app->id);
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            return app('AppointmentUserService')->listAppointments($app);
        });
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);
        if (request()->has('tracking_code')) {
            $this->edited['status'] = true;
            $this->edited['old_app'] = AppointmentUser::firstWhere('tracking_code', request()->get('tracking_code'));
        }
        if (request()->has('serviceId')) {
            $this->fetchData['service'] = Service::find(request()->get('serviceId'));
        }
    }
    public function render()
    {

        return view('appointmentuser::livewire.admin.add-appointment.specific-day-available-appointment');
    }
}
