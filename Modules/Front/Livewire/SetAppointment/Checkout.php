<?php

namespace Modules\Front\Livewire\SetAppointment;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
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
        'otherApp' => [
            'withOutMobile' => false,
            'withOutNational_code' => false,
        ]
    ];

    public function messages()
    {
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
                'form.otherApp.mobile'        => 'required_if:form.otherApp.withOutMobile,false|digits:11',
                'form.otherApp.insurence'      => 'nullable|string|max:225',

            ];
            $this->validate($rules);
        }
        if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_FOR_OTHERS_STATUS) &&  $this->form['app']['for'] === 'others') {
            // create a user
            $this->RegisterOtherAsUser();
        }
        // register the appointment
        $this->storeAppointment();
    }
    private function RegisterOtherAsUser()
    {
        $mobile = null;
        $national_code = null;
        if (isset($this->form['otherApp']['withOutMobile']) && $this->form['otherApp']['mobile']) {
            $mobile = $this->form['otherApp']['mobile'];
        }
        if (isset($this->form['otherApp']['withOutNational_code']) && $this->form['otherApp']['national_code']) {
            $national_code = $this->form['otherApp']['national_code'];
        }
        $pass = User::generatePassword();
        $userModel = [
            'mobile' => $mobile,
            'password' => $pass,
        ];
        $user = User::create($userModel);
        $user->document_number = User::generateDocumentNumber();
        if (isset($national_code)) {
            $user->national_code = $national_code;
        }
        $this->user = $user ;
    }

    private function storeappointment()
    {
        // TODO::this function copied from admin panel and not modified for this controlle !!!!!!!!!!!!!!!!!!!

        $user = $this->user ;

         // If he wants to take the appointmnet for someone else
         $someoneModel = null;
         $foHimself = 1;

         // main user data
         $mainUser = new UserModel(
             user: $user,
             firstName: $user->first_name,
             lastName: $user->last_name,
         );
         if (isset($this->appTime)) {
             $start_visit_time = explode(':', $this->appTime);
         }
         $start_visit_time = explode(':', $this->form['time']['from']);
         $appTime = Verta::parse($this->appDate)->tocarbon()->setTime($start_visit_time[0], $start_visit_time[1]);

         // full user model
         $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

         $appointment_type = $this->form['appType'] == 'main_app' ? AppointmentUserTypeEnum::MAIN__APPOINTMENT : AppointmentUserTypeEnum::BETWEEN_PATIENTS;
         $sms_status = $this->form['smsType'] == 'send' ? true : false;

         //check if operator
         if (isset($this->form['operator']) && !empty($this->form['operator'])) {
             $oprator =  $this->form['operator'];
         } else {
             $oprator = null;
         }
         // appointment model
         $appointmentModel = new AppointmentModel(
             timestamp: $appTime->timestamp,
             appointmentVia: AppointmentVia::BY_ADMIN,
             sendSmsToUser: $sms_status,
             serviceId: $this->appId->service?->id ?? $this->fetchData['service']?->id,
             placeId: $this->appId->place?->id ?? $this->placeId,
             agentId: auth()->user()->id,
             operatorId: $oprator,
             kind: isset($this->form['kind']) ? $this->form['kind'] : null,
             smsToDoctor: false,
             description: isset($this->form['description']) ? $this->form['description'] : '',
             type: $appointment_type,
             endTime: Carbon::createFromTimeString($this->form['time']['until'])->toTimeString(),
         );

         $detail = [];


         if (setting(SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT) != null && isset($this->form['registerWithoutPayment']) && $this->form['registerWithoutPayment'] == 'true') {
             $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
             $detail['wait_for_payment'] = true;
         }
         $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
         Cache::forget('appointmentList.' . $this->appId);

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
