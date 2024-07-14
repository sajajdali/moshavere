<?php

namespace Modules\Front\Livewire\Auth\User;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Session;

#[Layout('front::layouts.app')]
#[Title('ورود')]
class Registration extends Component
{

    #[Locked]
    public array $fetchData = [];
    public array $form = [];
    public User $user;

    public function messages()
    {
        return [
            'form.first_name.required' => 'لطفا نام را وارد کنید!',
            'form.last_name.required'  => 'لطفا نام خانوادگی را وارد کنید!',
            'form.gender.required'     => 'لطفا جنسیت را انتخاب کنید',
            'form.first_name.max'      => 'نام بیش از اندازه طولانی است',
            'form.last_name.max'       => 'نام بیش از اندازه طولانی است',
            'form.gender.max'          => 'لطفا جنسیت را انتخاب کنید',
            'form.first_name.string'   => 'فرمت وارد شده صحیح نیست',
            'form.last_name.string'    => 'فرمت وارد شده صحیح نیست',
            'form.gender.string'       => 'فرمت وارد شده صحیح نیست',
            'form.email.email'         => 'ایمیل وارد شده صحیح نیست',
        ];
    }
    public function completeUserInfo()
    {
        $this->validate([
            'form.first_name' => 'required|string|max:225',
            'form.last_name'  => 'required|string|max:225',
            'form.gender'     => 'required|string|max:225',
            'form.email'      => 'nullable|email|max:225',
        ]);
        $this->user->first_name = $this->form['first_name'];
        $this->user->last_name = $this->form['last_name'];
        $this->user->gender = $this->form['gender'];
        if (isset($this->form['national_code'])) {
            $this->user->national_code = $this->form['national_code'];
        }
        if (isset($this->form['email'])) {
            $userMOdel = User::find($this->user->id);
            $userMOdel->update(['email' => $this->form['email']]);
        }
        $intendedUrl = Session::pull('url.intended', route('front.homePage'));
        if (isset($intendedUrl)) {
            session()->forget('url.intended');
            return redirect()->intended($intendedUrl);
        }
        return redirect()->route('front.homePage');
    }
    public function mount()
    {
        $this->user  = auth()->user();
    }
    public function render()
    {
        return view('front::livewire.auth.user.registration');
    }
}
