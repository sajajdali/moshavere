<?php

namespace Modules\Front\Livewire\Auth\Doctor;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('front::layouts.app')]
#[Title('ورود پزشک')]
class DoctorLogin extends Component
{

    public array $form = [];
    public array $fetchData = [];

    public function DocLoginForm()
    {
        $this->validate([
            'form.mobile' => 'required|string|digits:11',
            'form.password' => 'required|min:4',
        ]);

        if (Auth::attempt(['mobile' => $this->form['mobile'], 'password' => $this->form['password']])) {
            // Authentication passed
            $user = Auth::user();
            if (isset($user->ban_user) && $user->ban_user == true) {
               return  $this->fetchData['alert'] = 'پروفایل شما هنوز تایید نشده است!';
            }
            return redirect()->route('admin.dashboard')->with('success', 'خوش آمدید');
        } else {
            $this->addError('authError', 'مشخصات وارد شده صحیح نیست');
        }
    }
    public function render()
    {
        return view('front::livewire.auth.doctor.doctor-login');
    }
}
