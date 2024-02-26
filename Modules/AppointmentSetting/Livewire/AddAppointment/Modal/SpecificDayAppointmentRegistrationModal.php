<?php

namespace Modules\AppointmentSetting\Livewire\AddAppointment\Modal;

use Livewire\Component;

class SpecificDayAppointmentRegistrationModal extends Component
{
    public array $setProp = [
        "number" => null,
        "document" => null,
        "first_name" => null,
        "last_name" => null,
    ];

    public $step = 1;

    public function numberSet()
    {
        if ($this->step == 1) {
            $this->validate([
                'setProp.number' => 'required_if:setProp.document,null|digits:11|nullable',
                'setProp.document' => 'required_if:setProp.number,null',
            ]);
            $this->step  = $this->step + 1;
        } elseif ($this->step == 2) {
            $this->validate([
                'setProp.first_name' => 'required',
                'setProp.last_name' => 'required',
            ]);
            $this->dispatch('closeModal', true);
            $this->step = 1;
            $this->setProp = [
                "number" => null,
                "document" => null,
            ];
        }
    }
    public function messages()
    {
        return [
            'setProp.number.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'setProp.number.digits' => 'شماره موبایل صحیح نیست!',
            'setProp.document.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'setProp.first_name.required' => 'وارد کردن نام الزامی است',
            'setProp.last_name.required' => 'وارد کردن نام خانوادگی الزامی است',
        ];
    }
    public function render()
    {
        return view('appointmentsetting::livewire.add-appointment.modal.specific-day-appointment-registration-modal');
    }
}
