<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Illuminate\Validation\Rule;
use Modules\User\Entities\User;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSettingTime;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingInterface;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingPaymentStatus;

class GeneralSetting extends Component
{
    public ?User $user;
    public array $fetchData = [];
    public $isEdited = false;
    public $isSpecialTimeEdited = false;
    public ?AppointmentSetting $appointment_setting;
    /*
    minDayAvaialbe
    maxDayAvaialbe
    maxAvailabeAppointment
    */
    public array $form = [
        'minDayAvaialbe' => 0,
        'maxDayAvaialbe' => 90,
        'avtive' => true,
    ];

    //day property
    public array $timeFrame = [];
    public array $counter = [
        'saturday'   => 1,
        'sunday'     => 1,
        'monday'     => 1,
        'tuesday'    => 1,
        'wednesday'  => 1,
        'thursday'   => 1,
        'friday'     => 1,
    ];



    public function addCounter($day)
    {
        $this->counter[$day] = $this->counter[$day] + 1;
        $this->render();
    }
    public function removeCounter($day)
    {
        $this->counter[$day] = $this->counter[$day] - 1;
        $this->render();
    }
    public function rules()
    {
        //validation for each day time frame
        $dayRules = [];
        foreach ($this->counter as $dayName => $counter) {
            //if that day is active
            if (isset($this->form['visitType'][$dayName]) && (isset($this->form['visitType'][$dayName]) == 'true')) {
                for ($i = 0; $i < $counter; $i++) {
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.start'] = 'required';
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.end']   = 'required';
                }
            }
        }
        $rules  = [
            'form.timeFrame'                      => 'required',
            'form.visitType.absente'              => 'required_without_all:form.visitType.online',
            'form.visitType.online'               => 'required_without_all:form.visitType.absente',
            'form.visitTime'                      => 'required',
            'form.minDayAvaialbe'                 => 'required|integer',
            'form.maxDayAvaialbe'                 => 'required|integer',
            'form.maxAvailabeAppointment.eachDay' => 'required_if:form.maxAvailabeAppointment.status,true',
            'form.maxAvailabeAppointment.totall'  => 'required_if:form.maxAvailabeAppointment.status,true',
            'form.cancel.day'                     => 'required_if:form.cancel.status,true',
            'form.endAppointment.date'            => 'required_if:form.endAppointment.status,true',
            'form.onlinePayment.Price'            => [
                Rule::requiredIf(function () {
                    return isset($this->form['onlinePayment']['status']) && $this->form['onlinePayment']['status'] == true &&
                        ((isset($this->form['onlinePayment']['online']['status']) && $this->form['onlinePayment']['online']['status'] == true) ||
                            (isset($this->form['onlinePayment']['voip']['status']) && $this->form['onlinePayment']['voip']['status'] == true)
                        );
                }),
            ],
            'form.onlinePayment.notPayingStatus'  => [
                Rule::requiredIf(function () {
                    return isset($this->form['onlinePayment']['status']) && $this->form['onlinePayment']['status'] == true &&
                        ((isset($this->form['onlinePayment']['online']['status']) && $this->form['onlinePayment']['online']['status'] == true)
                        );
                }),
            ],
        ];

        return array_merge($dayRules,  $rules);
    }
    public function saveSetting()
    {
        $this->validate();
        $endAppointmentTime =  isset($this->form['endAppointment']['date']) ? Verta::parse($this->form['endAppointment']['date'])->toCarbon() : null;
        $detail = [
            'visit_type_absente'             => $this->form['visitType']['absente'],
            'visit_type_online'              => $this->form['visitType']['online'],
            'maxAvailabeAppointment-eachDay' => $this->form['maxAvailabeAppointment']['eachDay'],
            'maxAvailabeAppointment-totall'  => $this->form['maxAvailabeAppointment']['totall'],
            'payment'                        => [
                'online'  => [
                    'status'                     => isset($this->form['onlinePayment']['online']['status']) ? $this->form['onlinePayment']['online']['status'] : null,
                    'notPayinStatus'             => isset($this->form['onlinePayment']['notPayingStatus']) ?  $this->form['onlinePayment']['notPayingStatus']  : null,
                ],
                'voip'   => [
                    'status'                     => isset($this->form['onlinePayment']['voip']['status'])   ? $this->form['onlinePayment']['voip']['status']   : null
                ],
                'price'                          => isset($this->form['onlinePayment']['Price']) ? $this->form['onlinePayment']['Price'] : null,
            ]
        ];
        $online_payment = $this->form['onlinePayment']['online']['tatus'] ?? null;
        $updateOrCreateModel = [
            'user_id'               =>  $this->user->id,
            'service_id'            =>  $this->fetchData['service_id'],
            'place_id'              =>  $this->fetchData['place'],
            'time_for_visit'        =>  isset($this->form['visitTime']) ? $this->form['visitTime'] : null,
            'min_day_active'        =>  $this->form['minDayAvaialbe'],
            'max_day_active'        =>  $this->form['maxDayAvaialbe'],
            'cancellation_by_user'  =>  $this->form['cancel']['day'] ?? null,
            'last_day_active'       =>  $endAppointmentTime,

            'active_payment'        =>  isset($this->form['onlinePayment']['online']) ? AppintmentSettingPaymentStatus::tryFrom($this->form['onlinePayment']['status']) : 0,
            'interference'          =>  isset($this->form['interference']['status'])  ? AppintmentSettingInterface::tryFrom($this->form['interference']['status']) : AppintmentSettingInterface::getDefault(),
            'avtive'                =>  ActiveEnum::tryFrom($this->form['avtive']),
            'detail'                =>  $detail,
        ];

        if ($this->isEdited) {
            if ($this->isSpecialTimeEdited) {
                //is user editing the times for special section
                $this->appointment_setting->update($updateOrCreateModel);
            } else {
                $this->appointment_setting = AppointmentSetting::create($updateOrCreateModel);
            }
        } else {
            $this->appointment_setting =   AppointmentSetting::create($updateOrCreateModel);
        }
        $appointment_setting_times = [];
        foreach ($this->form['timeFrame'] as $dayName => $timeFrameForEachDay) {
            foreach ($timeFrameForEachDay as $key => $timeFrame) {
                $appointment_setting_times[] = [
                    'day_number' => AppintmentSettingDayNumber::getConstant($dayName),
                    'start_at'  => $timeFrame['start'],
                    'end_at'  => $timeFrame['end'],
                ];
            }
        }
        //store days and times
        if ($this->isEdited) {
            //is user editing the times
            if ($this->isSpecialTimeEdited) {
                //is user editing the times for special section
                foreach ($appointment_setting_times as $objectForStore) {
                    $this->appointment_setting->times()->updateOrCreate($objectForStore);
                }
            } else {
                foreach ($appointment_setting_times as $objectForStore) {
                    $this->appointment_setting->times()->create($objectForStore);
                }
            }
        } else {
            //user is not in edit mode
            foreach ($appointment_setting_times as $objectForStore) {
                $this->appointment_setting->times()->create($objectForStore);
            }
        }

        return redirect()->route('admin.appointment.doctor.list')->with('success', 'تنظیمات با موفقیت ذخیره شد');
    }
    private function fillTheForm()
    {
        if (empty($this->fetchData['service_id'])) {
            $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)
                ->whereNull('service_id')
                ->first();
        } else {
            $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)
                ->where('service_id', $this->fetchData['service_id'])
                ->first();
            $this->isSpecialTimeEdited = true;
            if (empty($apSet)) {
                $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)->whereNull('service_id')->first();
                $this->isSpecialTimeEdited = false;
            }
        }
        $this->appointment_setting = $apSet;

        $this->fillTheTime($apSet);
        $this->form['visitType']['absente']              = $apSet->detail['visit_type_absente'];
        $this->form['visitType']['online']               = $apSet->detail['visit_type_online'];
        $this->form['visitTime']                         = $apSet->time_for_visit;
        $this->form['minDayAvaialbe']                    = $apSet->min_day_active;
        $this->form['maxDayAvaialbe']                    = $apSet->max_day_active;
        $this->form['maxAvailabeAppointment']['eachDay'] = $apSet->detail['maxAvailabeAppointment-eachDay'];
        $this->form['maxAvailabeAppointment']['totall']  = $apSet->detail['maxAvailabeAppointment-totall'];
        $this->form['cancel']['day']                     = $apSet->cancellation_by_user ?? null;
        $this->form['avtive']                            = $apSet->avtive;

        if (isset($apSet->last_day_active)) {
            $this->form['endAppointment']['date'] = verta($apSet->last_day_active)->format('Y/m/d');
        }
        if (isset($apSet->active_payment)) {
            $this->form['onlinePayment']['status'] = $apSet->active_payment;
        }
        if (isset($apSet->interference)) {
            $this->form['interference']['status'] = $apSet->interference;
        }
        if (isset($apSet->detail['payment']['online'])) {
            $this->form['onlinePayment']['online']['status'] = $apSet->detail['payment']['online']['status'];
            $this->form['onlinePayment']['notPayingStatus']  = $apSet->detail['payment']['online']['notPayinStatus'];
        }
        if (isset($apSet->detail['payment']['voip'])) {
            $this->form['onlinePayment']['voip']['status'] = $apSet->detail['payment']['voip']['status'];
        }
        if (isset($apSet->detail['payment']['price'])) {
            $this->form['onlinePayment']['Price'] = $apSet->detail['payment']['price'];
        }
        // dd($apSet->detail);
        // you was here
        // check if payment store in database in proper way
        // check in edit mode , payment status of check boxes whould be true and inputs are visible
        //  check the js code for the input and call the dispatch method to appear them if its need to ;
        // TODO

    }
    private function fillTheTime($apSet)
    {
        foreach ($apSet->times->groupBy('day_number') as $dayNumber => $eachDayColleciton) {
            $this->form['visitType'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()] = true;
            // TODO:: you was here !! add amounth for checked the check box for each day
            foreach ($eachDayColleciton as $iterator => $value) {
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['start'] = $value->start_at;
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['end'] = $value->end_at;
            }
        }
    }

    public function mount()
    {
        $this->fetchData['user']            =  request()->route('user');
        $this->fetchData['service_id']      =  request()->route('service');
        $this->fetchData['place']           =  request()->route('place');
        if(isset(  $this->fetchData['place'])) {
            $this->fetchData['place'] =   $this->fetchData['place']->id ; 
        }
        if (!empty($this->fetchData['user'])) {
            $this->fetchData['doctor'] =  $this->fetchData['user'];
        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'پزشک مورد نظر یافت نشد');
        }
        //  check if the setting for sections exist
        //   wich means this section is not the first time that set setting for
        $check_Setting_exist = false;
        if (AppointmentSetting::where('user_id', $this->fetchData['user']->id)->get()->isNotEmpty()) {
            //setting exist
            $check_Setting_exist = true;
        }
        if (session()->has('resetTheSetting')) {
            $check_Setting_exist = false;
            $this->isEdited = true;
            $this->fillTheForm();
        }
        if ($check_Setting_exist) {
            return redirect()->route('admin.appointment.specialsection', ['user' => $this->fetchData['user']]);
        }
    }

    public function render()
    {

        return view('appointmentsetting::livewire.general-setting.general-setting');
    }
}
