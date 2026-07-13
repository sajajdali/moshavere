<?php

namespace Modules\Front\Livewire\Auth\User;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Api\Entities\AuthRequest;
use Illuminate\Support\Facades\Session;
use Modules\Setting\Enum\SettingKeyEnum;

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

    #[Locked]
    public bool $login_without_otp = false;

    #[Locked]
    public bool $callLoginTemplate = false;
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
            $mobile = data_get($this->form,'mobileNmber',null) ;
            if(! is_null($mobile)) {
                $this->form['mobileNmber'] = convert2english(trim($mobile)) ;
            }
            $this->validate([
                'form.mobileNmber' => 'required|digits:11|numeric'
            ]);
            $loginWithoutAuth = setting(SettingKeyEnum::LOGIN_WITHOUT_OTP) ?? false;
            if ($loginWithoutAuth) {
                $user =  AuthRequest::getUser($this->form['mobileNmber']);
                if ($user->isPatient()) {
                    auth()->login($user);
                    if (!isset($user->first_name)) {
                        session()->put('RegistrationUser', $user->id);
                        return redirect()->route('front.user.registration');
                    }

                    $intendedUrl = Session::pull('url.intended', route('front.homePage'));
                    if (isset($intendedUrl)) {
                        session()->forget('url.intended');
                        session()->flash('authsuccess', 'ورود با موفقیت انجام شد');
                        return redirect()->intended($intendedUrl);
                    }
                    return redirect()->route('front.homePage');
                } else {
                    return redirect()->route('front.login.doctor');
                }
            }
            $oldRequest = AuthRequest::where('mobile', $this->form['mobileNmber'])
                ->first();
            // check if request exist
            if (isset($oldRequest)) {
                // check for last request time
                if ($oldRequest->next_request_at->lessThanOrEqualTo(now())) {
                    AuthRequest::make($this->form['mobileNmber'], request()->ip());
                    $this->dispatch('startCountDown', true);
                } else {
                    $this->addError('form.mobileNmber', 'لطفا برای درخواست مجدد دو دقیقه صبر کنید!');
                }
            } else {
                // if record dose not exist
                AuthRequest::make($this->form['mobileNmber'], request()->ip());
            }
            $this->step = $this->step + 1;
            $this->dispatch('waitForCode', true);
        } elseif ($this->step == 2) {
            $code = data_get($this->form,'code',null) ;
            if(! is_null($code)) {
                $code = (string) convert2english(trim((string) $code));

                // Some mobile OTP/autofill implementations serialize a code such as
                // "0123" as the number 123. Restore the fixed-width OTP before validating.
                if (preg_match('/^\d{1,4}$/', $code)) {
                    $code = str_pad($code, 4, '0', STR_PAD_LEFT);
                }

                $this->form['code'] = $code;
            }
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
                    if (isset($intendedUrl)) {
                        session()->forget('url.intended');
                        session()->flash('authsuccess', 'ورود با موفقیت انجام شد');
                        return redirect()->intended($intendedUrl);
                    }
                    return redirect()->route('front.homePage');
                }
            } else {
                $this->addError('form.code', 'کد وارد شده صحیح نیست');
            }
        }
    }
    public function resendotpCode(int $sendForCall = 1)
    {
        if ($sendForCall === 2 && ! $this->callLoginTemplate) {
            $this->addError('form.code', 'امکان دریافت کد با تماس فعال نیست.');
            return;
        }

        $oldRequest = AuthRequest::where('mobile', $this->form['mobileNmber'])
            ->first();

        if ($oldRequest?->updated_at?->greaterThan(now()->subMinutes(2))) {
            $this->addError('form.code', 'برای ارسال مجدد کد باید دو دقیقه صبر کنید!');
            return;
        }

        AuthRequest::make($this->form['mobileNmber'], request()->ip(), $sendForCall);
        $this->dispatch('startCountDown', true);
    }

    public function resendCallOtpCode()
    {
        $this->resendotpCode(2);
    }

    public function changeNumber()
    {
        unset($this->form);
        $this->step = 1;
    }
    public function mount()
    {
        // when disable ui template
        if (disableUi()) {
            return redirect()->route('front.login.doctor');
        }
        if (request()->has('appointment')) {
            $this->fetchData['alert'] = 'برای ادامه مراحل دریافت نوبت لطفا ابتدا وارد شوید';
        }
        if (request()->has('comment')) {
            $this->fetchData['alert'] = 'برای گذاشتن نظر، لطفا ابتدا وارد شوید';
        }
        if (request()->has('favariteDr')) {
            $this->fetchData['alert'] = 'برای پسندیدن دکتر ، لطفا ابتدا  وارد شوید';
        }
        if (request()->has('cancelApp')) {
            $this->fetchData['alert'] = 'برای کنسل کردن نوبت لازم هست که وارد شوید!';
        }
        $this->login_without_otp = filter_var(setting(SettingKeyEnum::LOGIN_WITHOUT_OTP), FILTER_VALIDATE_BOOL);
        $callTemplate = setting(SettingKeyEnum::CALL_LOGIN_TEMPLATE);
        if (filled($callTemplate)) {
            $this->callLoginTemplate = true;
        }
    }
    public function render()
    {
        return view('front::livewire.auth.user.login');
    }
}
