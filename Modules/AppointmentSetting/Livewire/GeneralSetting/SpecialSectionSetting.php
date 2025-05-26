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
        $this->fetchData['SpecialAppointmentSetting'] = AppointmentSetting::where('user_id', $this->fetchData['user'])->whereNotNull('service_id')->whereHas('service')->get();
    }

    #[Computed]
    public function GeneralTimes()
    {
        if(isset($this->fetchData['GeneralAppointmentSetting'])){

            return  $this->fetchData['GeneralAppointmentSetting']->times()->whereNull('special_date')->get()->groupBy('day_number');
        }
        return null ;
    }
    #[Computed]
    public function SpecialTimes($AppointmentSettingId)
    {
        $appTime  = AppointmentSetting::find($AppointmentSettingId);
        if (!empty($appTime)) {
            return $appTime->times()->whereNull('special_date')->get()->groupBy('day_number');
        }
        return false;
    }

    #[On('docAndSection')]
    public function redirectToSetting($section, $doctor)
    {
        return redirect()->route('admin.appointment.setting.specialservice', [$doctor, $section]);
    }

    public function editSpecialSection($AppointmentSettingId)
    {
        $this->dispatch('redirectLoading',true);
        $appTime  = AppointmentSetting::find($AppointmentSettingId);
        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting.specialservice', [$appTime->user_id, $appTime->service_id, $appTime->place_id]);
    }

    #[On('delete')]
    public function deleteSpecialSectionSetting($model)
    {
        $appTime  = AppointmentSetting::find($model);
        $appTime->times->map(function ($q) {
            $q->delete();
        });
        $appTime->delete();
        return redirect()->route('admin.appointment.specialsection', $this->doctor)->with('success', 'تنظیمات با موفقیت  حذف شد');
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
