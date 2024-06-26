<?php

namespace Modules\Front\Livewire\Auth\Doctor;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Modules\User\Entities\User;
use Modules\Api\Entities\AuthRequest;
use Spatie\Permission\Models\Role;

#[Layout('front::layouts.app')]
#[Title('ورود پزشک')]

class DoctorRegistration extends Component
{
    public array $form = [];

    private function checkUserExist(): bool
    {
        $user = User::where('mobile', $this->form['mobile'])->exists();
        if ($user) {
            return true;
        }
        return false;
    }
    public function createDocotr()
    {
        $this->validate([
            'form.first_name' => 'required|string|min:2|max:225',
            'form.last_name'  => 'required|string|min:2|max:225',
            'form.password'   => 'required|string|min:4|max:225',
            'form.passwordConfirm' => 'required|string|min:4|max:225|same:form.password',
            'form.licenceNumber' => 'required|string|max:225',
            'form.mobile'          => 'required|string|digits:11',
            'form.description' => 'nullable|string|max:500',
        ]);
        if ($this->checkUserExist()) {
            $this->addError('authError', 'کاربر با این شماره همراه در سیستم ثبت شده است!');
        } else {
            $user = User::create([
                'mobile' => $this->form['mobile'],
                'password' => $this->form['password'],
            ]);
            $user->first_name = $this->form['first_name'];
            $user->last_name = $this->form['last_name'];
            $user->dr_licence_number = $this->form['licenceNumber'];
            $user->dr_licence_number = $this->form['licenceNumber'];
            $user->ban_user = true;
            $user->active_appointment = false;
            if (isset($this->form['description'])) {
                $user->drRegistrationDescription =  $this->form['description'];
            }

            $doctorRoles = Role::find(3);
            $user->syncRoles($doctorRoles);
            // alert 'درخواست شما ثبت شد'
        }
    }
    public function render()
    {
        return view('front::livewire.auth.doctor.doctor-registration');
    }
}
