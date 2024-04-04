<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Livewire\Attributes\Computed;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class SpecificDayAvailableAppointment extends Component
{
    //this propery shouldNOT exist in the final product
    public $tempMessage = null;
    public array $fetchData = ['showRegisterModal' => false];
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
        $this->dispatch('dateHasBeenChange', newDate: verta($this->fetchData['selectedDate'])->format('Y-m-d'));
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
        $privous_array = $this->fetchData['listOfAppointment'][($this->fetchData['showingAppointmentIndex'] + 1)];
        $privous_array_date = Carbon::parse($privous_array['date']);
        $this->fetchData['selectedDate'] = $privous_array_date;
        $this->dispatch('loadJs', true);
        $this->dateHasBeenChange();
        $this->render();
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
    }
    private function listOfAppointment($listOfAppointment)
    {

        $firstTwoEmpty = [];
        $report = $listOfAppointment['report'];
        $mainDaActive = $report['min_day_active'];

        $isDay   = Carbon::now()->format('Y-m-d');

        $result = [];
        $temPResult = [];
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
        $this->dispatch('time', from: $from, until: $until);
    }
    public function lunchAppModal()
    {
        $this->fetchData['showRegisterModal'] = true;
    }

    #[On('delete')]
    public function deleteAppointment($model)
    {
        $app = AppointmentUser::find($model);
        $app->delete();
        Cache::forget('appointmentList.' .   $this->fetchData['appId']);
        return redirect()->route('admin.appointment.add.specificday', ['appId' =>   $this->fetchData['appId'], 'date' => verta($this->fetchData['selectedDate'])->format('Y-m-d')])->with('success', 'نوبت با موفقیت افزوده شد');

        // AppointmentUser::$
    }
    public function editAppointment($id)
    {
        $app = AppointmentUser::find($id);
        return redirect()->route(
            'admin.appointment.add.specificday',
            [
                'appId'         =>   $this->fetchData['appId'],
                'date'          => verta($this->fetchData['selectedDate'])->format('Y-m-d'),
                'tracking_code' => $app->tracking_code
            ]
        );
    }
    private function editedMode()
    {
    }
    // when tracking_code is exist in url
    public function changeAppointmentDate($from, $until)
    {
        $this->edited['old_app']->update([
            'date_visit' => $this->fetchData['selectedDate']->todatetimestring(),
            'start_time' => $from,
            'end_time' => $until,
        ]);
        return redirect()->route('admin.appointment.add.specificday', [
            'appId' =>  $this->fetchData['appId'],
            'date' => verta($this->fetchData['selectedDate'])->format('Y-m-d')
        ])->with('success', 'نوبت با موفقیت تغییر کرد');
    }
    public function mount()
    {
        $app = AppointmentSetting::find(request()->route('appId'));
        $this->fetchData['doc'] = $app->user;
        $this->fetchData['appId'] = $app->id;
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
        // TODO::inere pak kon
        Cache::forget('appointmentList.' . $app->id);
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            return app('AppointmentUserService')->listAppointments($app);
        });
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);
        if (request()->has('tracking_code')) {
            $this->edited['status'] = true;
            $this->edited['old_app'] = AppointmentUser::firstWhere('tracking_code', request()->get('tracking_code'));
            $this->editedMode();
        }
    }
    public function render()
    {

        return view('appointmentuser::livewire.admin.add-appointment.specific-day-available-appointment');
    }
}
