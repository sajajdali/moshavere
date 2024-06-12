<?php

namespace Modules\Front\Livewire\SetAppointment;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

#[Layout('front::layouts.app')]
#[Title('ثبت نوبت')]
class ShowAvailableDayForDoctor extends Component
{

    public array $form = [];

    #[Locked]
    public array $fetchData = [];
    public ?AppointmentSetting $appointmentSetting;

    public function TimeForReservesation()
    {
        $this->validate(['form.time' => 'required|string']);
        dd('tes');
    }

    private function findFirstTreeAppointment($listOfAppointment)
    {
        // dd($listOfAppointment);
        $firstTwoEmpty = [];
        $report = $listOfAppointment['report'];
        $mainDaActive = $report['min_day_active'];
        $isDay   = verta()->addDays($mainDaActive)->day;
        $isMonth = verta()->addDays($mainDaActive)->month;
        $isYear  = verta()->addDays($mainDaActive)->year;

        $result = [];
        $maxDay = 2;
        $DaysDisplayed = 0;
        foreach ($listOfAppointment['data'] as $yeay => $monthWithAppointment) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($monthWithAppointment as $month => $appointments) {

                foreach ($appointments as $day => $appointment) {

                    if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
                        continue;
                    }
                    if ($appointment['empty_appoints'] <= 0 || $appointment['status'] == false) {
                        continue;
                    }
                    $dayNumber = $appointment['day_number_gmt'];
                    if ($DaysDisplayed > $maxDay) {
                        break 3;
                    }
                    $DaysDisplayed++;
                    foreach ($appointment['times'] as $increment =>  $time) {
                        if ($time['status']) {
                            $result[$dayNumber][] = [
                                'status' => true,
                                'day_of_week_name' =>  verta($time['timestamp'])->formatDifference(),
                                'day_name'            =>  verta($time['timestamp'])->format('l'),
                                'date_of_month'    =>  verta($time['timestamp'])->format('%d %B'),
                                'time_stamp' => $time['timestamp'],
                                'from' => substr($time['from'], 0, 5),
                                'until' => substr($time['until'], 0, 5),
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
                            // If two matches are found, break out of the loop
                        }
                    }
                }
            }
        }
        return $result;
    }
    private function getAvailableDay()
    {
        $appointmentSetting = AppointmentSetting::where('service_id', $this->fetchData['service']->id)
            ->where('place_id', $this->fetchData['places']->id)
            ->where('user_id', $this->fetchData['doc'])
            ->first();
        //check for general setting
        if (!isset($$appointmentSetting)) {
            $appointmentSetting = AppointmentSetting::where('user_id', $this->fetchData['doc']->id)->first();
        }
        $listOfAppointment = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
        $this->fetchData['firstTreeAvailableAppointment'] =  $this->findFirstTreeAppointment($listOfAppointment);
        $this->fetchData['appointmentSetting'] = $appointmentSetting->id;
    }
    public function mount()
    {
        $doc =  request()->input('doctor_id');
        $place =  request()->input('place_id');
        $service =  request()->input('service_id');
        if (!isset($doc) || empty($place) ||  empty($service)) {
            // redirect back with alert
            // return redirect()->route('front.homePage');
        }
        // TODO:: delete this section
        $doc = User::find(10);
        $Place = Place::first();
        $service = Service::first();

        $this->fetchData['doc']     =   $doc;
        $this->fetchData['places']  =   $Place;
        $this->fetchData['service']  =   $service;

        $this->getAvailableDay();
    }
    public function render()
    {
        return view('front::livewire.set-appointment.show-available-day-for-doctor');
    }
}
