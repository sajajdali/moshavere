<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Modules\User\Entities\User;

class GeneralSetting extends Component
{

    public array $fetchData = [];
    /*
    minDayAvaialbe
    maxDayAvaialbe
    maxAvailabeAppointment
    */
    public array $form = [
        'minDayAvaialbe' => 0,
        'maxDayAvaialbe' => 90,
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
    }

    public function mount()
    {
        $doctorId = request()->route('user');

        if (!empty($doctorId)) {
            $this->fetchData['doctor'] = User::find($doctorId);
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
