<?php

namespace Modules\Front\Livewire\Auth\User;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Api\Entities\AuthRequest;

#[Layout('front::layouts.app')]
#[Title('ورود')]
class Login extends Component
{

    #[Locked]
    public int $step = 1;

    public array $form = [];

    #[Locked]
    public $watitTime = null;
    public function messages()
    {
        return [
            'form.mobileNmber.required' => 'لطفا شماره همراه خود را وارد کنید!',
            'form.code.required' => 'لطفا شماره همراه خود را وارد کنید!',
            'form.mobileNmber.digits' => 'تعداد رقم های وارد شده صحیح نیست!',
            'form.code.digits' => 'کد تایید 4 رقمی میباشد!',
            'form.mobileNmber.numeric' => 'شماره موبایل باید به عدد باشد، لطفازبان کیبورد خود را به اینگلیسی تغییر دهید!',
            'form.code.string' => 'فرمت وارد شده قابل قبول نیست! لطفا زبان کیبورد را به اینگلیسی تغییر دهید!',
        ];
    }
    public function LoginAuthForm()
    {
        if ($this->step == 1) {
            $this->validate([
                'form.mobileNmber' => 'required|digits:11|numeric'
            ]);
            AuthRequest::make($this->form['mobileNmber'], request()->ip());
            $this->step = $this->step + 1;
            $this->dispatch('startCountDown', true);
        } elseif ($this->step == 2) {
            $this->validate([
                'form.code' => 'required|string|digits:4'
            ]);
            $status = AuthRequest::check($this->form['mobileNmber'], $this->form['code']);
            if ($status) {
                $user = AuthRequest::checkUserExist($this->form['mobileNmber']);
                if (isset($user)) {
                    $user =  AuthRequest::getUser($this->form['mobileNmber']);
                    auth()->login($user);
                    if (!isset($user->first_name)) {
                        session()->put('RegistrationUser',$user->id);
                        return redirect()->route('front.user.registration');
                    }
                    if (session()->has('LoginOrgin')) {
                        return redirect()->route(session()->get('LoginOrgin'));
                    } else {
                        return redirect()->route('front.homePage');
                    }
                }
            } else {
                $this->addError('form.code', 'کد وارد شده صحیح نیست');
            }
        }
    }
    public function resendotpCode()
    {
        $this->watitTime = now()->addMinutes(2);
        if (now()->lessThanOrEqualTo($this->watitTime)) {
            AuthRequest::make($this->form['mobileNmber'], request()->ip());
            $this->dispatch('startCountDown', true);
        } else {
            $this->addError('form.code', 'برای ارسال محدد کد باید دو دقیقه صبر کنید!');
        }
    }
    public function changeNumber()
    {
        unset($this->form);
        $this->step = 1;
    }
    public function render()
    {
        return view('front::livewire.auth.user.login');
    }
}
