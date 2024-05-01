<?php

namespace Modules\User\Livewire\Admin\User\DoctorInfo;

use Livewire\Component;
use Illuminate\Support\Arr;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
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
        if (isset($this->form['order'])) {
            $this->user->dr_order = $this->form['order'];
        }

        $this->user->active_appointment = $this->form['active'];
        $this->user->ban_user = $this->form['banUser'];

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
        if (isset($this->user->dr_address)) {
            $this->form['address'] = $this->user->dr_address;
        }
        if (isset($this->user->dr_order)) {
            $this->form['order'] = $this->user->dr_order;
        }
        if (isset($this->user->active_appointment) &&  $this->user->ban_user != 1) {
            $this->form['active'] =  false;
        } else {
            $this->form['active'] = true;
        }
        if (isset($this->user->ban_user) &&  $this->user->ban_user == 1) {
            $this->form['banUser'] =   true;
        } else {
            $this->form['banUser'] =   false;
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
