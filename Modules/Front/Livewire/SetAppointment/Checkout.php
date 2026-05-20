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
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
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
                'form.otherApp.gender'         => 'required|string|max:225',
                'form.otherApp.national_code' => 'required_if:form.otherApp.withOutNational_code,false|max:225',
                'form.otherApp.mobile'        => 'required|digits:11',
                'form.otherApp.insurence'      => 'nullable|string|max:225',

            ];
            $this->validate($rules);
        }
        if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_FOR_OTHERS_STATUS) &&  $this->form['app']['for'] === 'others') {
            // create a user
            $this->RegisterOtherAsUser();
        }
        if (isset($this->fechData['isOnline'])  &&  $this->fechData['isOnline'] &&   isset($this->fetchData['appSetting']->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP])) {
            $maxAppointmentForEachDay = (int) $this->fetchData['appSetting']->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP];
            if (AppointmentOnline::whereHas('appointmentUser', function ($q) {
                return $q->activeAppointmentStatus();
            })->whereDate('date_visit', now()->addDay())->count() >= $maxAppointmentForEachDay) {
                // appoitment reach their limit
                return $this->err = 'ظرفیت های نوبت آنلاین به اتمام رسیده است ، لطفا در روز دیگری تلاش کنید';
            }
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
        if (isset($this->form['otherApp']['withOutMobile']) && $this->form['otherApp']['withOutMobile'] != true) {
            if (isset($this->form['otherApp']['mobile']) && $this->form['otherApp']['mobile'] != null) {
                $mobile = $this->form['otherApp']['mobile'];
            }
        }
        if (isset($this->form['otherApp']['withOutNational_code']) && $this->form['otherApp']['withOutNational_code'] != true) {
            if (isset($this->form['otherApp']['national_code']) && $this->form['otherApp']['national_code'] != null) {
                $national_code = $this->form['otherApp']['national_code'];
            }
        }
        $pass = User::generatePassword();
        $userModel = [
            'mobile' => $mobile,
            'password' => $pass,
        ];
        if (User::where('mobile', 'LIKE', "%{$mobile}%")->exists()) {
            $user = User::where('mobile', 'LIKE', "%{$mobile}%")->first();
        } else {
            $user = User::create($userModel);
        }
        $user->first_name = $this->form['otherApp']['first_name'];
        $user->last_name = $this->form['otherApp']['last_name'];
        $user->document_number = User::generateDocumentNumber();
        if (isset($national_code)) {
            $user->national_code = $national_code;
        }
        $this->user = $user;
    }
    private function checkForActiveAppointment(): bool
    {
        if (setting(SettingKeyEnum::APPOINTMENT_MORE_THAT_ONE_PER_DAY)) {
            // user can reserve multiple appointment
            return true;
        }
        $user_selected_date = Carbon::createFromTimestamp($this->fetchData['app_start_time'])->toDateString();
        if ($this->fetchData['isOnline']) {
            $kind = AppointmentUserKindEnum::ONLINE;
        } else {
            $kind = AppointmentUserKindEnum::IN_PERSION;
        }
        // Check if user has an appointment on the selected date
        $existingAppointment = $this->user->appointments()
            ->whereDate('date_visit', $user_selected_date)
            ->where('kind', $kind)
            ->whereIn('status', [
                AppointmentUserStatusEnum::STATUS_PENDING,
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                AppointmentUserStatusEnum::STATUS_ATTENDED,
                AppointmentUserStatusEnum::STATUS_NOT_ATTENDED,
                AppointmentUserStatusEnum::STATUS_MONITORING,
                AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED,
            ])->exists();
        if ($existingAppointment) {
            $this->err = 'شما یک نوبت فعال در این روز دارید!';
            return false;
        }
        return true;
    }
    private function storeappointment()
    {
        /* @var $user User */
        $user = $this->user;
        // If he wants to take the appointmnet for someone else
        $someoneModel = null;
        $foHimself = 1;

        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $user->firstName,
            lastName: $user->lastName,
        );
        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

        if ($this->fetchData['isOnline']) {
            $kind = AppointmentUserKindEnum::ONLINE;
        } else {
            $kind = AppointmentUserKindEnum::IN_PERSION;
        }
        $smsToDoctor = true;
        if (isset($this->fetchData['appSetting']->doctor->drStoreAppSms) && $this->fetchData['appSetting']->doctor->drStoreAppSms == true) {
            $smsToDoctor = false;
        }
        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $this->fetchData['app_start_time'],
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $this->fetchData['appSetting']->service?->id ?? $this->fetchData['service']->id,
            placeId: $this->fetchData['appSetting']->place?->id ?? $this->fetchData['places']->id,
            agentId: auth()->user()->id,
            operatorId: data_get($this->fetchData, 'operator', null),
            kind: $kind,
            smsToDoctor: $smsToDoctor,
            description: isset($this->form['description']) ? $this->form['description'] : '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: Carbon::createFromTimestamp($this->fetchData['app_end_time'], 'Asia/Tehran')->toTimeString(),
        );

        $detail = [];
        if (isset($this->fetchData['segmentsId']) && $this->fetchData['segmentsId'] != null) {
            $detail['segments_ids'] = $this->fetchData['segmentsId'];
        }
        // sms Template
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
            $appointmentSetting = AppointmentSetting::find($this->fetchData['appSetting']->id);
            $date = Carbon::createFromTimestamp($this->fetchData['app_start_time'], 'Asia/Tehran')->toDateString();
            $appointmentSetting->runGenerateCacheJob($date);

            $routeParameters = ['tracking_code' => $storeAppointment['detail']['tracking_code']];
            $appointmentUser = AppointmentUser::find($storeAppointment['detail']['appointment_user_id']);
            if (
                setting(SettingKeyEnum::GO_TO_PAYMENT_DIRECTLY) &&
                $appointmentUser?->status == AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT
            ) {
                $routeParameters['direct_payment'] = true;
            }
            return redirect()->route('front.setAppointment.detail', $routeParameters);
        } else {
            $this->err = $storeAppointment['message'];
        }
    }

    public function mount()
    {
        // get app time from route
        $startTime =  $this->fetchData['app_start_time']  =  request()->input('start_time');
        $this->fetchData['app_end_time']    =  request()->input('end_time');
        $isOnlineRoute                      = request()->boolean('isOnline');
        $this->fetchData['segmentsId']      = request()->get('segmentId', null);
        $this->fetchData['isOnline']        = filter_var($isOnlineRoute, FILTER_VALIDATE_BOOL);

        $doc     = filter_var(request()->get('doctor_id', null), FILTER_SANITIZE_NUMBER_INT);
        $place   = filter_var(request()->get('place_id', null), FILTER_SANITIZE_NUMBER_INT);
        $service = filter_var(request()->get('service_id', null), FILTER_SANITIZE_NUMBER_INT);
        $this->fetchData['operator'] = request()->get('operator', null);
        $this->fetchData['doc'] = User::findOrFail($doc);
        $this->fetchData['places'] =  place::findOrFail($place);
        $this->fetchData['service'] = Service::findOrFail($service);
        if (isset($this->fetchData['operator']) && ! empty($this->fetchData['operator'])) {
            $o = User::findOrFail((int) $this->fetchData['operator']);
            if (! $o->isOperator()) {
                return abort(404);
            }
        }
        if (
            ! $this->fetchData['places']->users()->where('user_id', $this->fetchData['doc']->id)->exists() ||
            ! $this->fetchData['service']->user()->where('user_id', $this->fetchData['doc']->id)->exists()
        ) {
            return abort(404);
        }
        if (empty($startTime) || empty($this->fetchData['app_end_time'])) {
            return redirect()->route('front.setAppointment.days', [
                'doctor_id' => $doc,
                'place_id' => $place,
                'service_id' => $service
            ])->with('error', 'لطفا مجدد تاریخ را انتخاب کنید!');
        }
        $this->fetchData['date_for_blade'] = Carbon::createFromTimestamp($startTime, 'Asia/Tehran');
        if ($this->fetchData['date_for_blade']->lt(\now())) {
            return abort(404);
        }
        // check for login
        if (auth()->check()) {
            $this->user =  auth()->user();
        } else {
            $parameter = [
                'doctor_id'  => $this->fetchData['doc']->id,
                'place_id'   => $this->fetchData['places']->id,
                'service_id' => $this->fetchData['service']->id,
                'start_time' => $this->fetchData['app_start_time'],
                'end_time'   => $this->fetchData['app_end_time'],
                'segments'   => $this->fetchData['segmentsId'],
                'operator'   => $this->fetchData['operator']
            ];
            $route = route('setAppointment.checkout', $parameter);
            session()->put('url.intended', $route);
            return redirect()->route('front.login.user', ['appointment' => 'true']);
        }
        $this->fetchData['appSetting'] = AppointmentSetting::findSettingId(
            $this->fetchData['doc']->id,
            $this->fetchData['service']->id,
            $this->fetchData['places']->id
        );
        if (filter_var(setting(SettingKeyEnum::GO_TO_PAYMENT_DIRECTLY), FILTER_VALIDATE_BOOL)) {
            if ($this->checkForActiveAppointment()) {
                // register the appointment
                $this->storeAppointment();
            }
        }
    }
    public function render()
    {
        return view('front::livewire.set-appointment.checkout');
    }
}
