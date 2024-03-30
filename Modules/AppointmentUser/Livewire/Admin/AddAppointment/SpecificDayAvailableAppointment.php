<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Livewire\Attributes\Computed;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class SpecificDayAvailableAppointment extends Component
{
    //this propery shouldNOT exist in the final product
    public $tempMessage = null;
    public array $fetchData = [];
    public array $form = [];
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
    #[On('closeModal')]
    public function addLoading()
    {
        //this function is just for appearing loading and should be deleted
        sleep(2);
        $this->tempMessage = 'تغییرات با موفقیت اعمال شد';
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
    public function mount()
    {
        $app = AppointmentSetting::find(request()->route('appId'));
        $this->fetchData['doc'] = $app->user;
        if (!empty(request()->route('date'))) {
            $this->fetchData['selectedDate']  = Verta::parse(request()->route('date'))->toCarbon();
        } else {
            return redirect()->back()->with('error', 'لطفا مجدد تاریخ را انتخاب کنید');
        }
        if ((request()->has('time'))) {
            $this->fetchData['time'] =  request()->get('time');
            //lunch modal
        }
        // TODO::inere pak kon
        Cache::forget('appointmentList.' . $app->id);
        $this->fetchData['RawlistOfAppointment']  = Cache::rememberForever('appointmentList.' . $app->id, function () use ($app) {
            return app('AppointmentUserService')->listAppointments($app);
        });
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);
    }
    public function render()
    {

        return view('appointmentuser::livewire.admin.add-appointment.specific-day-available-appointment');
    }
}
