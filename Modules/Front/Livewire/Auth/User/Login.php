<?php

namespace Modules\Front\Livewire\Auth\User;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Api\Entities\AuthRequest;
use Illuminate\Support\Facades\Session;

#[Layout('front::layouts.app')]
#[Title('ورود')]
class Login extends Component
{

    #[Locked]
    public int $step = 1;

    #[Locked]
    public array $fetchData = [];

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
            $oldRequest = AuthRequest::where('mobile', $this->form['mobileNmber'])
                ->first();
            // check if request exist
            if (isset($oldRequest)) {
                // check for last request time
                if ($oldRequest->next_request_at->lessThanOrEqualTo(now())) {
                    AuthRequest::make($this->form['mobileNmber'], request()->ip());
                    $this->dispatch('startCountDown', true);
                } else {
                    $this->addError('form.mobileNmber', 'لطفا برای درخواست مجدد چند دقیقه صبر کنید!');
                }
            } else {
                // if record dose not exist
                AuthRequest::make($this->form['mobileNmber'], request()->ip());
            }
            $this->step = $this->step + 1;
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
                        session()->put('RegistrationUser', $user->id);
                        return redirect()->route('front.user.registration');
                    }
                    $intendedUrl = Session::pull('url.intended', route('front.homePage'));
                    if(isset($intendedUrl)) {
                        session()->forget('url.intended') ;
                        return redirect()->intended($intendedUrl);
                    }
                    return redirect()->route('front.homePage');
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
    public function mount() {
        if(request()->has('appointment')){
            $this->fetchData['alert'] = 'برای ادامه مراحل دریافت نوبت لطفا ابتدا وارد شوید';
        }
        if(request()->has('comment')){
            $this->fetchData['alert'] = 'برای گذاشتن نظر، لطفا ابتدا وارد شوید';
        }
        if(request()->has('favariteDr')){
            $this->fetchData['alert'] = 'برای پسندیدن دکتر ، لطفا ابتدا  وارد شوید';
        }
        if(request()->has('cancelApp')){
            $this->fetchData['alert'] = 'برای کنسل کردن نوبت لازم هست که وارد شوید!';
        }
    }
    public function render()
    {
        return view('front::livewire.auth.user.login');
    }
}
