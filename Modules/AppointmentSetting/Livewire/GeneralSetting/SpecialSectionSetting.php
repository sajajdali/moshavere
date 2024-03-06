<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class SpecialSectionSetting extends Component
{
    public array $form =  [];
    public array $fetchData =  [];
    public $doctor;

    public function editGeneralSetting()
    {

        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting', ['user' => $this->doctor->id, 'edit' => 'true']);
    }
    private function fillTheFechData()
    {
        $this->fetchData['GeneralAppointmentSetting'] = AppointmentSetting::where('user_id', $this->fetchData['user'])->whereNull('service_id')->first();
        $this->fetchData['SpecialAppointmentSetting'] = AppointmentSetting::where('user_id', $this->fetchData['user'])->whereNotNull('service_id')->get();
    }

    #[Computed]
    public function GeneralTimes()
    {
        return  $this->fetchData['GeneralAppointmentSetting']->times->groupBy('day_number');
    }
    #[Computed]
    public function SpecialTimes()
    {
        if ($this->fetchData['SpecialAppointmentSetting']->isNotEmpty()) {
            return  $this->fetchData['SpecialAppointmentSetting']->times->groupBy('day_number');
        }
        return false;
    }

    #[On('docAndSection')]
    public function redirectToSetting($section, $doctor)
    {
        return redirect()->route('admin.appointment.setting.specialservice',[$doctor,$section]);
    }
    public function mount()
    {
        $this->fetchData['user'] = request()->route('user');
        if (!empty($this->fetchData['user'])) {
            $this->doctor = User::find($this->fetchData['user']);
        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'پزشک مورد نظر یافت نشد');
        }
        $this->fillTheFechData();
    }
    public function render()
    {
        return view('appointmentsetting::livewire.general-setting.special-section-setting');
    }
}
