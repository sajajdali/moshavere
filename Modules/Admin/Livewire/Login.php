<?php

namespace Modules\Admin\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[title('ورود')]
#[Layout('admin::layouts.login')]
class Login extends Component
{
    #[Rule('required',message: 'شماره همراه را وارد کنید')]
    #[Rule('digits:11',message: 'شماره همراه کمتر از 11 رقم است')]
    #[Rule('numeric',message: 'شماره همراه به درستی وارد نشده است')]
    public $mobile;
    #[Rule('required',message: 'رمز عبور را وارد کنید')]
    public $password;

    public $recaptcha;

    public $message;

    public function mount()
    {
        if (auth()->check() && auth()->user()->can('ADMIN_ACCESS')) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
    }

    public function requestLogin()
    {
        $this->message = '';
        $this->validate();
        // TODO::add recapcha
        // $recaptcha = new \ReCaptcha\ReCaptcha(config('app.recaptcha.secret_key'));
        // $resp = $recaptcha
        //     ->setExpectedAction('login')
        //     ->verify($this->recaptcha, request()->ip());
        // if ($resp->isSuccess()) {
            if (auth()->attempt(['mobile' => $this->mobile, 'password' => $this->password], true)) {
                return redirect()->route('admin.dashboard');
            }
            $this->message = 'ایمیل یا رمز عبور اشتباه است';
        // } else {
        //     $this->message = 'خطا در سرور! مجدد تلاش کنید.';
        // }
        $this->dispatch('resetReCaptcha');
    }

    public function render()
    {
        return view('admin::livewire.login');
    }
}
