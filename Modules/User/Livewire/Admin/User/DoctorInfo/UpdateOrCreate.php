<?php

namespace Modules\User\Livewire\Admin\User\DoctorInfo;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Modules\Service\app\Models\Service;
use Modules\User\Enum\UserSpecialityType;
use Modules\Speciality\app\Models\Speciality;

class UpdateOrCreate extends Component
{
    public $isEdited;
    public User $user;
    public array $form;
    public array $fetchData = [];

    public function storeDocInfo()
    {
        if (isset($this->form['specility'])) {
            $r =  $this->user->specialities()->sync(array_values($this->form['specility']));
        }
        if (isset($this->form['specialitiesType'])) {
            $this->user->speciality_type = UserSpecialityType::tryFrom($this->form['specialitiesType'])->value;
        } else {
            $this->user->speciality_type = UserSpecialityType::DOCTOR->value;
        }
        if (isset($this->form['biography'])) {
            $this->user->dr_biography = $this->form['biography'];
        }
        if (isset($this->form['licenceNumber'])) {
            $this->user->dr_licence_number = $this->form['licenceNumber'];
        }
        if (isset($this->form['places'])) {
            $this->user->places()->sync(array_values($this->form['places']));
        }
        if (isset($this->form['services'])) {
            $this->user->services()->sync(array_values($this->form['services']));
        }
        if (isset($this->form['address'])) {
            $this->user->dr_address = $this->form['address'];
        }
        if (isset($this->form['dr_display_address'])) {
            $this->user->dr_display_address = $this->form['dr_display_address'];
        }
        if (isset($this->form['dr_display_mobile'])) {
            $this->user->dr_display_mobile =  $this->form['dr_display_mobile'];
        }
        if (isset($this->form['drDisplayNavigation'])) {
            $this->user->dr_display_navigation =     $this->form['drDisplayNavigation'];
        }
        if (isset($this->form['drDisplayExperince'])) {
            $this->user->dr_display_experince =     $this->form['drDisplayExperince'];
        }
        if (isset($this->form['drDisplayDiscription'])) {
            $this->user->dr_display_discription =     $this->form['drDisplayDiscription'];
        }
        if (isset($this->form['order'])) {
            $this->user->dr_order = $this->form['order'];
        }
        if (isset($this->form['drRate'])) {
            $this->user->dr_rate = $this->form['drRate'];
        }
        if (isset($this->form['showDocInEmergencyVisit']['status']) && $this->form['showDocInEmergencyVisit']['status']) {
            $this->user->dr_emergencyvisit_order    = $this->form['showDocInEmergencyVisit']['order'];
            $this->user->dr_emergencyvisit_status   = $this->form['showDocInEmergencyVisit']['status'];
        } else {
            $this->user->dr_emergencyvisit_order    = false;
            $this->user->dr_emergencyvisit_status   = false;
        }
        if (isset($this->form['ShowInIntrodocs']['status']) && $this->form['ShowInIntrodocs']['status']) {

            $this->user->dr_info_status = $this->form['ShowInIntrodocs']['status'];
            $this->user->dr_info_order  = $this->form['ShowInIntrodocs']['order'];
        } else {
            $this->user->dr_info_status = false;
            $this->user->dr_info_order  = false;
        }
        if (isset($this->form['drWaitingTime'])) {
            $this->user->dr_waiting_time = $this->form['drWaitingTime'];
        }
        if (isset($this->form['active'])) {
            $this->user->active_appointment = $this->form['active'] == false ? 0 : 1;
        }
        if (isset($this->form['drBanner'])) {
            $this->user->dr_banner = $this->form['drBanner'];
        }
        if (isset($this->form['drStoreAppSms'])) {
            $this->user->drStoreAppSms = $this->form['drStoreAppSms'];
        }else{
            $this->user->drStoreAppSms = false;
        }
        $this->user->ban_user = $this->form['banUser'];
        Cache::forget('emergency_doctors');
        Cache::forget('Introduction_doctors');

        return redirect()->route('admin.user.index')->with('success', 'اطلاعات پزشک با موفقیت ثبت شد');
    }

    private function fillTheInputs()
    {
        $this->fetchData['specialities']     = Speciality::all();
        $this->fetchData['specialitiesType'] = UserSpecialityType::cases();
        $this->fetchData['services']         = Service::all();
        $this->fetchData['places']           = Place::all();
        if ($this->user->specialities->isNotEmpty()) {
            $this->form['specility'] = $this->user->specialities->pluck('id')->toArray();
        }
        if (isset($this->user->speciality_type)) {
            $this->form['specialitiesType'] =  UserSpecialityType::tryFrom($this->user->speciality_type)->value;
        } else {
            $this->form['specialitiesType'] = UserSpecialityType::DOCTOR->value;
        }
        if (isset($this->user->dr_biography)) {
            $this->form['biography'] =   $this->user->dr_biography;
        }
        if (isset($this->user->dr_licence_number)) {
            $this->form['licenceNumber'] = $this->user->dr_licence_number;
        }
        if (isset($this->user->dr_display_address)) {
            $this->form['dr_display_address'] = $this->user->dr_display_address;
        }
        if (isset($this->user->dr_display_mobile)) {
            $this->form['dr_display_mobile'] = $this->user->dr_display_mobile;
        }
        if (isset($this->user->dr_display_navigation)) {
            $this->form['drDisplayNavigation'] = $this->user->dr_display_navigation;
        }
        if (isset($this->user->dr_display_experince)) {
            $this->form['drDisplayExperince'] = $this->user->dr_display_experince;
        }
        if (isset($this->user->dr_display_discription)) {
            $this->form['drDisplayDiscription'] = $this->user->dr_display_discription;
        }
        if (isset($this->user->dr_order)) {
            $this->form['order'] = $this->user->dr_order;
        }
        if (isset($this->user->dr_banner)) {
            $this->form['drBanner'] = $this->user->dr_banner;
        }
        if (isset($this->user->active_appointment) &&  $this->user->active_appointment != 1) {
            $this->form['active'] =  false;
        } else {
            $this->form['active'] = true;
        }
        if (isset($this->user->ban_user) &&  $this->user->ban_user == 1) {
            $this->form['banUser'] =   true;
        } else {
            $this->form['banUser'] =   false;
        }
        if ($this->user->dr_rate) {
            $this->form['drRate'] = $this->user->dr_rate;
        }
        if (isset($this->user->dr_emergencyvisit_status)) {
            if ($this->user->dr_emergencyvisit_status == 0) {
                $this->form['showDocInEmergencyVisit']['status'] =  false;
            } else {
                $this->form['showDocInEmergencyVisit']['status'] =  true;
            }
            if (isset($this->user->dr_emergencyvisit_order)) {
                $this->form['showDocInEmergencyVisit']['order'] =   $this->user->dr_emergencyvisit_order;
            }
        }
        if (isset($this->user->dr_info_status)) {
            if ($this->user->dr_info_status == 0) {
                $this->form['ShowInIntrodocs']['status'] =  false;
            } else {
                $this->form['ShowInIntrodocs']['status'] =  true;
            }
            if (isset($this->user->dr_info_order)) {
                $this->form['ShowInIntrodocs']['order'] =  $this->user->dr_info_order;
            }
        }
        if (isset($this->user->dr_waiting_time)) {
            $this->form['drWaitingTime'] = $this->user->dr_waiting_time;
        }
    }
    public function mount()
    {
        $user = request()->route('user');
        if ($user instanceof User) {
            $this->authorize('update', $user);
            $this->isEdited = true;
            $this->user = $user;
        }
        $this->fillTheInputs();
        if ($this->user->services->isNotEmpty()) {
            $this->form['services'] = $this->user->services->pluck('id')->toArray();
        }
        if ($this->user->places->isNotEmpty()) {
            $this->form['places'] = $this->user->places->pluck('id')->toArray();
        }
    }
    public function render()
    {
        return view('user::livewire.admin.user.doctor-info.update-or-create');
    }
}
