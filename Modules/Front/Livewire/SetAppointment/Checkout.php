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
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;

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
    public string $err = '';
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

        // check user not have active appointment for that day
        if ($this->checkForActiveAppointment()) {

            // register the appointment
            $this->storeAppointment();
        }
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
        $this->user = $user;
    }
    private function checkForActiveAppointment():bool
    {
        if(setting(SettingKeyEnum::APPOINTMENT_MORE_THAT_ONE_PER_DAY)) {
            // user can reserve multiple appointment
            return true ;
        }
        $user_selected_date = Carbon::createFromTimestamp($this->fetchData['app_start_time'])->toDateString();

        // Check if user has an appointment on the selected date
        $existingAppointment = $this->user->appointments()->whereDate('date_visit', $user_selected_date)->exists();
        if ($existingAppointment) {
            $this->err = 'شما یک نوبت فعال در این روز دارید!';
            return false;
        }
        return true;
    }
    private function storeappointment()
    {

        $user = $this->user;
        // If he wants to take the appointmnet for someone else
        $someoneModel = null;
        $foHimself = 1;

        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $user->first_name,
            lastName: $user->last_name,
        );
        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

        //check if operator
        if (isset($this->form['operator']) && !empty($this->form['operator'])) {
            $oprator =  $this->form['operator'];
        } else {
            $oprator = null;
        }

        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $this->fetchData['app_start_time'],
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $this->fetchData['appSetting']->service?->id ?? $this->fetchData['service']->id,
            placeId: $this->fetchData['appSetting']->place?->id ?? $this->fetchData['places']->id,
            agentId: auth()->user()->id,
            kind: AppointmentUserKindEnum::IN_PERSION,
            smsToDoctor: false,
            description: isset($this->form['description']) ? $this->form['description'] : '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: Carbon::createFromTimestamp($this->fetchData['app_end_time'])->toTimeString(),
        );

        $detail = [];

        // sms temolate
        if (
            $this->fetchData['appSetting']->detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS] == true &&
            $this->fetchData['appSetting']->detail[AppointmentSetting::PAYMENT][AppointmentSetting::NOT_PAYING_STATUS] == 'dontSubmit'
        ) {
            // if payment was active
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
        } else {
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
        }

        $storeAppointment = app('AppointmentUserService')->storeAppointment($this->fetchData['appSetting'], $userModelAppointment, $appointmentModel, $detail);
        if ($storeAppointment['status']) {
            Cache::forget('appointmentList.' . $this->fetchData['appSetting']->id);
            return redirect()->route('front.setAppointment.detail', ['tracking_code' => $storeAppointment['detail']['tracking_code']]);
        } else {
            $this->err = $storeAppointment['message'];
        }
    }


    public function mount()
    {
        // get app time from route
        $this->fetchData['app_start_time'] = request()->input('start_time');
        $this->fetchData['app_end_time'] =  request()->input('end_time');
        if (empty($this->fetchData['app_start_time']) || empty($this->fetchData['app_end_time'])) {
            return redirect()->route('setAppointment.days')->with('error', 'لطفا مجدد تاریخ را انتخاب کنید!');
        }
        $this->fetchData['date_for_blade'] = Carbon::createFromTimestamp($this->fetchData['app_start_time']);
        $doc =  request()->input('doctor_id');
        $place =  request()->input('place_id');
        $service =  request()->input('service_id');
        if (!isset($doc) || empty($place) ||  empty($service)) {
            // redirect back with alert
            // return redirect()->route('front.homePage');
        }
        $this->fetchData['doc']      =   User::find($doc);
        $this->fetchData['places']   =  place::find($place);
        $this->fetchData['service']  =   Service::find($service);

        if (!isset($this->fetchData['doc']) || empty($this->fetchData['places']) ||  empty($this->fetchData['service'])) {
            // redirect back with alert
            // TODO::insert alert
            return redirect()->back();
        }

        // check for login
        if (auth()->check()) {
            $this->user =  auth()->user();
        } else {
            $parameter = [
                'doctor_id' => $this->fetchData['doc']->id,
                'place_id'  => $this->fetchData['places']->id,
                'service_id' => $this->fetchData['service']->id,
                'start_time' => $this->fetchData['app_start_time'],
                'end_time' => $this->fetchData['app_end_time'],
            ];
            $route = route('setAppointment.checkout', $parameter);
            session()->put('url.intended', $route);
            return redirect()->route('front.login.user', ['appointment' => 'true']);
        }

        // find app special setting
        $this->fetchData['appSetting'] = AppointmentSetting::where('service_id', $this->fetchData['service']->id)
            ->where('place_id', $this->fetchData['places']->id)
            ->where('user_id', $this->fetchData['doc'])
            ->first();
        //check for general setting
        if (!isset($this->fetchData['appSetting'])) {
            $this->fetchData['appSetting'] = AppointmentSetting::where('user_id', $this->fetchData['doc']->id)
                ->whereNull('place_id')
                ->whereNull('service_id')
                ->first();
        }
    }
    public function render()
    {
        return view('front::livewire.set-appointment.checkout');
    }
}
