<?php

namespace Modules\Front\Livewire\Auth\Doctor;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('front::layouts.app')]
#[Title('ورود پزشک/مدیر')]
class DoctorLogin extends Component
{

    public array $form = [];
    public array $fetchData = [];

    public function DocLoginForm()
    {
        $identifier = trim((string) ($this->form['identifier'] ?? ''));
        $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $this->validate([
            'form.identifier' => $loginField === 'email'
                ? 'required|string|email:rfc|max:255'
                : 'required|string|digits:11',
            'form.password' => 'required|min:4',
        ], [
            'form.identifier.required' => 'شماره موبایل یا ایمیل را وارد کنید.',
            'form.identifier.email' => 'ایمیل واردشده معتبر نیست.',
            'form.identifier.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
        ]);

        if (Auth::attempt([$loginField => $identifier, 'password' => $this->form['password']])) {
            // Authentication passed, regenerate session token
            session()->regenerate();
            $user = Auth::user();
            if (isset($user->ban_user) && $user->ban_user == true) {
                return  $this->fetchData['alert'] = 'پروفایل شما هنوز تایید نشده است!';
            }
            return redirect()->route('admin.dashboard')->with('success', 'خوش آمدید');
        } else {
            $this->addError('authError', 'مشخصات وارد شده صحیح نیست');
        }
    }

    public function mount()
    {
        if (!Auth::guest() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'شما داخل پنل مدیریت هستید');
        }
    }
    public function render()
    {

        return view('front::livewire.auth.doctor.doctor-login');
    }
}
