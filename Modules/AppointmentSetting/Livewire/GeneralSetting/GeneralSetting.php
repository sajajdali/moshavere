<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Illuminate\Validation\Rule;
use Modules\User\Entities\User;
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
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.start'] = 'required|date_format:H:i';
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.end']   = 'required|date_format:H:i';
                }
            }
        }
        $rules  = [
            'form.visitType.absente'    => 'required_without_all:form.visitType.online',
            'form.visitType.online'     => 'required_without_all:form.visitType.absente',
            'form.visitTime'            => 'required',
            'form.minDayAvaialbe'       => 'required|integer',
            'form.maxDayAvaialbe'       => 'required|integer',
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
        $updateOrCreateModel = [
            'user_id'               =>  $this->user->id,
            'service_id'            =>  $this->fetchData['service_id'],
            'place_id'              =>  $this->fetchData['place_id'],
            'time_for_visit'        =>  isset($this->form['visitTime']) ? $this->form['visitTime'] : null,
            'min_day_active'        =>  $this->form['minDayAvaialbe'],
            'max_day_active'        =>  $this->form['maxDayAvaialbe'],
            'cancellation_by_user'  =>  $this->form['cancel']['day'] ?? null,
            'last_day_active'       =>  $this->form['endAppointment']['date'] ?? null,
            'active_payment'        =>  isset($this->form['onlinePayment']['status']) ? AppintmentSettingPaymentStatus::tryFrom($this->form['onlinePayment']['status']) : 0,
            'interference'          =>  isset($this->form['interference']['status']) ? AppintmentSettingInterface::tryFrom($this->form['interference']['status']) : AppintmentSettingInterface::getDefault(),
            'avtive'                =>  ActiveEnum::tryFrom($this->form['avtive']),
        ];

        $DayTime = [];
        foreach ($this->counter as $dayName => $counter) {
            //if that day is active
            if (isset($this->form['visitType'][$dayName]) && (isset($this->form['visitType'][$dayName]) == 'true')) {
                for ($i = 0; $i < $counter; $i++) {
                    $DayTime[$dayName][$i] = [
                        'start' =>  $this->form['timeFrame'][$dayName][$i]['start'],
                        'end' => $this->form['timeFrame'][$dayName][$i]['end']
                    ];
                }
            }
        }

        if ($this->isEdited) {
            $this->appointment_setting =  AppointmentSetting::update($updateOrCreateModel);
        } else {
            $this->appointment_setting =   AppointmentSetting::create($updateOrCreateModel);
        }
        $dayMolde = [];
        foreach ($DayTime as $dayName => $values) {
            if (count($values) > 1) {
                $array_is_one = false;
                foreach ($values as  $index =>  $eacchDayTime) {
                    $dayMolde[$index] = [
                        'appointment_setting_id' => $this->appointment_setting->id,
                        'day_number' =>  AppintmentSettingDayNumber::getConstant($dayName),
                        'start_at' =>   $eacchDayTime['start'],
                        'end_at' =>     $eacchDayTime['end'],
                    ];
                }
            } else {
                $array_is_one = true;
                $dayMolde = [
                    'appointment_setting_id' => $this->appointment_setting->id,
                    'day_number' =>  AppintmentSettingDayNumber::getConstant($dayName),
                    'start_at' =>   $values[0]['start'],
                    'end_at' =>     $values[0]['end'],
                ];
            }
        }
        if ($this->isEdited) {
            AppointmentSettingTime::update($dayMolde);
        } else {
            if ($array_is_one) {
                AppointmentSettingTime::create($dayMolde);
            } else {
                foreach ($dayMolde as $model) {
                    AppointmentSettingTime::create($model);
                }
            }
        }
    }

    public function mount()
    {
        $this->fetchData['user'] = request()->route('user');
        $this->fetchData['service_id']     = request()->has('service') ? request()->route('service') : null;
        $this->fetchData['place_id']        = request()->has('place_id') ? request()->route('place_id') : null;
        if (!empty($this->fetchData['user'])) {
            $this->fetchData['doctor'] =  $this->fetchData['user'];
        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'پزشک مورد نظر یافت نشد');
        }

        /**
         * check if the setting for sections exist
         * wich means this section is not the first time that set setting for
         **/
        $check_Setting_exist = false;
        //TODO:: check if this Dr has setting and if it has , set this variable true ;
        if (request()->has('edit')) {
            $check_Setting_exist = false;
        }
        if ($check_Setting_exist) {
            return redirect()->route('admin.appointment.specialsection', ['user' => $doctorId]);
        }
    }

    public function render()
    {

        return view('appointmentsetting::livewire.general-setting.general-setting');
    }
}
