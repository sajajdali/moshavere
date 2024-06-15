<?php

namespace Modules\Front\Livewire\SetAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;


#[Layout('front::layouts.app')]
#[Title('ثبت نوبت')]
class Checkout extends Component
{

    #[Locked]
    public User $user;

    #[Locked]
    public array  $fetchData = [];
    public array $form = [
        'app' => ['for' => 'mySelf'],
        'termAndCondition' => true,
    ];

    public function messages() {
        return [
            'form.otherApp.first_name.required' => 'وارد کردن نام الزامی است!',
            'form.otherApp.last_name.required' => 'وارد کردن نام خانوادگی الزامی است!',
            'form.otherApp.gender.required' => 'انتخاب جنسیت الزامی است!',
            'form.otherApp.national_code.required_if' => 'وارد کردن کد ملی الزامی است!',
            'form.otherApp.mobile.required_if' => 'وارد کردن شماره موبایل الزامی است!',
            'form.otherApp.insurence.string' => 'فرمت وارد شده قابل قبول نیست!',
            'form.otherApp.insurence.max' => 'تعداد کاراکتر وارد شده بیش از حد مجاز است!',
            'form.otherApp.first_name.max' => 'تعداد کاراکتر وارد شده بیش از حد مجاز است!',
            'form.otherApp.last_name.max' => 'تعداد کاراکتر وارد شده بیش از حد مجاز است!',
            'form.otherApp.gender.max' => 'تعداد کاراکتر وارد شده بیش از حد مجاز است!',
            'form.otherApp.national_code.max' => 'تعداد کاراکتر وارد شده بیش از حد مجاز است!',
            'form.otherApp.mobile.digits' => 'تعداد شماره وارد شده صحیح نیست!',

        ];
    }
    public function setAppointment()
    {
        if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_FOR_OTHERS_STATUS) &&  $this->form['app']['for'] === 'others') {
            $rules = [
                'form.otherApp.first_name' => 'required|string|max:225',
                'form.otherApp.last_name' => 'required|string|max:225',
                'form.otherApp.gender' => 'required|string|max:225',
                'form.otherApp.national_code' => 'required_if:form.otherApp.withOutNational_code,false|max:225',
                'form.otherApp.mobile'      => 'required_if:form.otherApp.withOutMobile,false | digits:11 ',
                'form.otherApp.insurence'      => 'nullable|string|max:225',

            ];
            $this->validate($rules);
        }
    }
    public function mount()
    {
        $selected_time = request()->input('appointment_time');
        if (!isset($selected_time) && empty($selected_time)) {
            return redirect()->route('setAppointment.days')->with('error', 'لطفا مجدد تاریخ را انتخاب کنید!');
        }
        // TODO::redirect user to Login with sesstion to redirect back to this page
        // $this->user = auth()->user() ;
        // if(!auth()->check()) {
        //     return redirect()->route('login');
        // }
        $doc =  request()->input('doctor_id');
        $place =  request()->input('place_id');
        $service =  request()->input('service_id');
        if (!isset($doc) || empty($place) ||  empty($service)) {
            // redirect back with alert
            // return redirect()->route('front.homePage');
        }

        $this->user = User::find(4);
        $doc = User::find(10);
        $Place = Place::first();
        $service = Service::first();

        $this->fetchData['doc']         =   $doc;
        $this->fetchData['places']      =   $Place;
        $this->fetchData['service']     =   $service;
        $this->fetchData['appTime']     =   Carbon::createFromTimestamp($selected_time);
        $this->fetchData['appSetting'] = AppointmentSetting::where('service_id', $this->fetchData['service']->id)
            ->where('place_id', $this->fetchData['places']->id)
            ->where('user_id', $this->fetchData['doc'])
            ->first();
        //check for general setting
        if (!isset($this->fetchData['appSetting'])) {
            $this->fetchData['appSetting'] = AppointmentSetting::where('user_id', $this->fetchData['doc']->id)->first();
        }
    }

    public function render()
    {
        return view('front::livewire.set-appointment.checkout');
    }
}
