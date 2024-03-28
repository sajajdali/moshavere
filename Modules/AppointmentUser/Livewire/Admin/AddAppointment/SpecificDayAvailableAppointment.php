<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
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

    public function loadDifferentDayDetail()
    {
        // if date has been change , this functio would be call
        $this->fetchData['selectedDate']      =  Verta::parse($this->form['changeDate'])->tocarbon();
        $this->fetchData['listOfAppointment'] = $this->listOfAppointment($this->fetchData['RawlistOfAppointment']);
        $this->render();
    }
    public function previousDay()
    {
        if (isset($this->fetchData['pdate']) && !empty($this->fetchData['pdate'])) {
            $this->fetchData['pdate'] = $this->fetchData['pdate']->subDay();
        } else {
            $this->fetchData['pdate'] = $this->fetchData['selectedDate']->subDay();
        }
        dd($this->fetchData['RawlistOfAppointment']);
        $this->ListOfAppointmentForPreviousDay($this->fetchData['RawlistOfAppointment']);
    }
    public function nextDay()
    {
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

        $selectedDate = Carbon::parse($this->fetchData['selectedDate']); // Assuming $this->fetchData['selectedDate'] is a string representing the selected date

        $firstTwoEmpty = [];
        $result = [];

        foreach ($listOfAppointment['data'] as $year => $months) {
            foreach ($months as $month => $appointments) {
                foreach ($appointments as $day => $appointment) {
                    $appointmentDate = Carbon::parse($appointment['day_number_gmt']);
                    $index = 0;
                    // if ($appointmentDate->startOfDay()->lt($selectedDate->startOfDay()) || $appointment['empty_appoints'] <= 0 || !$appointment['status']) {
                    //     continue;
                    // }
                    $appointment['is_date_selected'] = false;
                    if ($appointmentDate->eq($selectedDate)) {
                        $appointment['is_date_selected'] = true;
                    }
                    $this->fetchData['selectedDateShamsiValue'] = verta($appointment['day_number_gmt'])->format('Y/m/d');
                    foreach ($appointment['times'] as $time) {
                        if ($time['status']) {
                            // $result['date'] = $appointment['day_number_gmt'] ;
                            $result[$index]['is_active'] = false;
                            $result[$index]['date'] = $appointmentDate->format('Y/m/d');
                            if ($appointmentDate->eq($this->fetchData['selectedDate'])) {
                                $result[$index]['is_active'] = true;
                                $this->fetchData['selectedDateShamsiValue'] = $appointmentDate->format('Y/m/d');
                            }
                            $result[$index]['times'][] = [
                                'status' => true,
                                'time_stamp' => $time['timestamp'],
                                'from' => $time['from'],
                                'until' => $time['until'],
                            ];
                            if (count($firstTwoEmpty) < 2) {
                                $vertaDateTime = Verta::createTimestamp($time['timestamp']);
                                $firstTwoEmpty[] = [
                                    'persian_date' => $vertaDateTime->format('ساعت H روز l m/d'),
                                    'time_stamp' => $time['timestamp'],
                                    'from' => $time['from'],
                                    'until' => $time['until'],
                                ];
                            }
                        }
                        $index = $index + 1;
                    }
                }
            }
        }
        return $result;
    }

    public function mount()
    {
        $app = AppointmentSetting::find(request()->route('appId'));
        if (!empty(request()->route('date'))) {
            $this->fetchData['selectedDate']  = Verta::parse(request()->route('date'))->toCarbon();
        } else {
            return redirect()->back()->with('error', 'لطفا مجدد تاریخ را انتخاب کنید');
        }
        if ((request()->has('time'))) {
            $this->fetchData['time'] = Carbon::createFromTimestamp((int) request()->get('time'))->format('H:i');
            //lucch modal
        }
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
