<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingPaymentStatus;

class SpecificDayAppointmentRegistrationModal extends Component
{
    public array $form = [
        "number" => null,
        "document" => null,
        "first_name" => null,
        "last_name" => null,
        "appType" => 'main_app',
        "smsType" => 'send',
    ];
    // "appType" => keys : main , subMainApp ;
    public array $fetchData = [];
    public $step = 1;
    public $appId;
    public $appTime;
    public $appDate;

    public function dismisModal()
    {
        $this->form = [
            "number" => null,
            "document" => null,
            "first_name" => null,
            "last_name" => null,
            "appType" => true,
            "smsType" => true,
        ];
        if (isset($this->fetchData['user'])) {
            unset($this->fetchData['user']);
        }
        if (isset($this->form['time']['from'])) {
            unset($this->form['time']['from']);
            unset($this->form['time']['until']);
        }

        $this->step = 1;
    }
    public function privousStep()
    {
        if ($this->step == 2) {
            $this->form = [
                "number" => null,
                "document" => null,
                "first_name" => null,
                "last_name" => null,
                "appType" => 'main_app',
                "smsType" => 'send',
            ];
            if (isset($this->fetchData['user'])) {
                unset($this->fetchData['user']);
            }
            $this->step = 1;
        } elseif ($this->step == 3) {
            $this->step = 2;
        }
    }
    public function numberSet()
    {
        if ($this->step == 1) {
            $this->findeOrCreateUser();
        } elseif ($this->step == 2) {
            $this->validate([
                'form.first_name' => 'required',
                'form.last_name' => 'required',
                'form.time.from' => 'required',
            ]);

            if (!isset($this->fetchData['user'])) {
                $this->createUser();
            }
            $this->storeAppointmentByAdmin();
        } elseif ($this->step == 3) {
            $this->storeApp();
            $this->step = 1;
            $this->form = [
                "number" => null,
                "document" => null,
            ];
        }
    }

    #[On('dateHasBeenChange')]
    public function changeAppDate($newDate)
    {
        $this->appDate = $newDate;
    }
    private function createUser()
    {
        $this->fetchData['user'] = User::create([
            'mobile' => $this->fetchData['tempUser']['number'],
            'password' => uniqId(),
        ]);
        $this->fetchData['user']->first_name = $this->form['first_name'];
        $this->fetchData['user']->last_name = $this->form['last_name'];
        if (isset($this->form['document_number'])) {
            $this->fetchData['user']->document_number = $this->form['document_number'];
        }
    }
    private function findeOrCreateUser()
    {
        $this->validate([
            'form.number' => 'required_if:form.document_number,null|digits:11|nullable',
            'form.document_number' => 'required_if:form.number,null',
        ]);
        if (isset($this->form['number'])) {
            $user = User::where('mobile', $this->form['number'])->first();
            if (!empty($user)) {
                $this->fetchData['user'] = $user;
                $this->fillUserInputs();
                $this->step  = $this->step + 1;
            } else {
                $this->fetchData['tempUser']['number'] = $this->form['number'];
                if (isset($this->form['document_number'])) {
                    $this->fetchData['tempUser']['document_number'] = $this->form['document_number'];
                }
                $this->step  = $this->step + 1;
            }
        } elseif (isset($this->form['document_number'])) {
            $user =  User::whereHas('metas', function ($q) {
                return $q->where('meta_key', UserMetaEnum::DOCUMENT_NUMBER)->where('meta_value', $this->form['document_number']);
            })->first();
            if (!empty($user)) {
                $this->fetchData['user'] = $user;
                $this->fillUserInputs();
                $this->step  = $this->step + 1;
            } else {
                $this->addError('userNotExists', 'کاربری یافت نشد ، لطفا برای ایجاد کاربر با این شماره پرونده ، شماره تلفن را نیز وارد کنید!');
            }
        }
    }
    private function fillUserInputs()
    {
        if (isset($this->fetchData['user'])) {
            $this->form['document']   =  $this->fetchData['user']->document_number;
            $this->form['first_name'] =  $this->fetchData['user']->first_name;
            $this->form['last_name']  =  $this->fetchData['user']->last_name;
        }
    }
    #[On('time')]
    public function setTime($from, $until)
    {
        $this->form['time']['from'] = $from;
        $this->form['time']['until'] = $until;
    }
    public function IsthisTimeAvaialable($from, $until)
    {
        $appSetting = AppointmentSetting::find($this->appId);
        return  app('AppointmentUserService')->isAppointmentTimeAvailable(
            $from,
            $until,
            Verta::parse($this->appDate)->toCarbon()->format('Y/m/d'),
            $appSetting
        );
    }

    public function storeAppointmentByAdmin()
    {
        $from = Carbon::createFromTimeString($this->form['time']['from']);
        $until =  Carbon::createFromTimeString($this->form['time']['until']);
        if ($from->greaterThan($until)) {
            return $this->addError('form.time.from', 'زمان شروع نوبت نباید بزرگ تر از زمان پایان باشد');
        } else {
            $is_time_free = $this->IsthisTimeAvaialable($from->toDateString(), $until->toDateString());
            if ($is_time_free) {
                $this->storeApp();
            } else {
                $this->step = 3;
            }
            $this->render();
        }
    }
    private function storeApp()
    {
        $user = $this->fetchData['user'];
        $appointmentSetting = AppointmentSetting::findOrFail($this->appId);
        // If he wants to take the turn for someone else
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
        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $appTime->timestamp,
            appointmentVia: AppointmentVia::BY_ADMIN,
            sendSmsToUser: $sms_status,
            serviceId: $this->appId->service?->id ?? null,
            placeId: $this->appId->place?->id ?? null,
            description: isset($this->form['description']) ? $this->form['description'] : '',
            type: $appointment_type,
            endTime: Carbon::createFromTimeString($this->form['time']['until'])->toTimeString(),
        );


        $detail = [];
        $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
        Cache::forget('appointmentList.' . $this->appId);
        return redirect()->route('admin.appointment.add.specificday', ['appId' => $this->appId, 'date' => $this->appDate])->with('success', $storeAppointment['message']);
    }

    public function closeModal()
    {
        $this->dispatch('closeModal', true);
        $this->form = [
            "number" => null,
            "document" => null,
            "first_name" => null,
            "last_name" => null,
            "appType" => true,
            "smsType" => true,
        ];
        unset($this->fetchData['user']);
    }
    public function messages()
    {
        return [
            'form.number.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'form.number.digits' => 'شماره موبایل صحیح نیست!',
            'form.document_number.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'form.first_name.required' => 'وارد کردن نام الزامی است',
            'form.last_name.required' => 'وارد کردن نام خانوادگی الزامی است',
            'form.time.from.required' => 'زمان نوبت به درستی انتخاب نشده است!',
        ];
    }

    public function mount()
    {

        if (isset($this->appId)) {
            $app =  AppointmentSetting::find($this->appId);
        }
        if (isset($this->appTime) && !empty($this->appTime)) {
            $this->form['time']['from'] = $this->appTime;
            $this->form['time']['until'] = Carbon::createFromTimeString($this->appTime)->copy()->addMinutes($app->time_for_visit)->toTimeString();
        }
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.modal.specific-day-appointment-registration-modal');
    }
}
