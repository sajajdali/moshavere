<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Matrix\Operators\Operator;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Absence\app\Models\Absence;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
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
        "kind" => AppointmentUserKindEnum::IN_PERSION,
    ];
    // "appType" => keys : main , subMainApp ;
    public array $fetchData = [];
    public $step = 1;
    public $appId;
    public $appTime;
    public $serviceId;
    public $placeId;
    public $appDate;
    public $segmentId;

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
                'form.time.until' => 'required',
            ]);

            if (!isset($this->fetchData['user'])) {
                $this->createUser();
            }
            if (isset($this->fetchData['operators'])) {
                $this->checkForAvaiableOperator();
                $this->step = 3;
            } else {
                return $this->storeAppointmentByAdmin();
            }
        } elseif ($this->step == 3) {
            return $this->storeAppointmentByAdmin();
        } elseif ($this->step == 4) {
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
            Verta::parse($this->appDate)->toCarbon()->toDateString(),
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
            if (setting(\Modules\Setting\Enum\SettingKeyEnum::ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL)) {
                $is_time_free = $this->IsthisTimeAvaialable($from->toTimeString(), $until->toTimeString());
                if ($is_time_free) {
                    return $this->storeApp();
                } else {
                    $this->step = 4;
                }
            } else {
                return $this->storeApp();
            }
        }
    }
    private function storeApp()
    {
        //check if payment is active and sms template exist for it
        if ((setting(SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT) != null  &&
            isset($this->form['registerWithoutPayment']) &&  $this->form['registerWithoutPayment'] != 'false')) {
            if (setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT) == null) {
                $this->addError('form.registerWithoutPayment', true);
            }
        }
        $user = $this->fetchData['user'];
        $appointmentSetting = AppointmentSetting::findOrFail($this->appId);
        //check for appointment kind

        if ($appointmentSetting->detail[AppointmentSetting::VISIT_TYPE_ONLINE] && $appointmentSetting->detail[AppointmentSetting::VISIT_TYPE_INPERSON]) {
            if (!isset($this->form['kind']) || empty($this->form['kind'])) {
                return $this->addError('AppKind', 'لطفا نوع نوبت را انتخاب کنید');
            }
        } else {
            if ($appointmentSetting->detail[AppointmentSetting::VISIT_TYPE_ONLINE]) {
                $this->form['kind'] = AppointmentUserKindEnum::ONLINE;
            } elseif ($appointmentSetting->detail[AppointmentSetting::VISIT_TYPE_INPERSON]) {
                $this->form['kind'] = AppointmentUserKindEnum::IN_PERSION;
            } else {
                $this->form['kind'] = AppointmentUserKindEnum::VOIP;
            }
        }

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
        $sms_status = false;
        if ($this->form['smsType'] == 'send') {
            $sms_status = true;
        }
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
            serviceId: $this->serviceId ?? $this->fetchData['service']?->id,
            placeId: $this->placeId->id,
            agentId: auth()->user()->id,
            operatorId: $oprator,
            kind: isset($this->form['kind']) ? $this->form['kind'] : null,
            smsToDoctor: true,
            description: isset($this->form['description']) ? $this->form['description'] : '',
            type: $appointment_type,
            endTime: Carbon::createFromTimeString($this->form['time']['until'])->toTimeString(),
        );

        $detail = [];
        if (setting(\Modules\Setting\Enum\SettingKeyEnum::ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL)) {
            $detail['store_from_admin_panel'] = true;
        }
        if (isset($this->segmentId) && $this->segmentId != null) {
            $detail['segments_ids'] = $this->segmentId;
        }
        if (setting(SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT) != null && isset($this->form['registerWithoutPayment']) && $this->form['registerWithoutPayment'] == 'true') {
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
            $detail['wait_for_payment'] = true;
        }
        $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
        return redirect()->route('admin.appointment.add.specificday', [
            'serviceId' => $this->fetchData['service']->id,
            'placeId' => $this->placeId,
            'appId' => $this->appId,
            'date' => $this->appDate,
            'segmentItemId' => $this->segmentId,
            'storedApp' =>  data_get($storeAppointment,'detail.appointment_user_id',null)
        ])->with('success', value: $storeAppointment['message']);
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
    private function checkForAvaiableOperator()
    {
        // $selected_date_visit = (Verta::parse($this->appDate)->toCarbon());
        // if (isset($this->appTime)) {
        //     $start_visit_time = explode(':', $this->appTime);
        // }
        // $start_visit_time = explode(':', $this->form['time']['from']);
        // $appTime = Verta::parse($this->appDate)->tocarbon()->setTime($start_visit_time[0], $start_visit_time[1])->toTimeString();
        // $untilTimeString = $this->form['time']['until'];
        // if ($untilTimeString) {
        //     $endTime = Carbon::createFromTimeString($untilTimeString)->toTimeString();
        // } else {
        //     $endTime = Carbon::parse($appTime)->addMinutes($this->fetchData['app']->time_for_visit)->toTimeString();
        // }

        // $this->fetchData['appointmentUser_with_operator'] = AppointmentUser::whereNotNull('operator_id')
        //     ->whereDate('date_visit', $selected_date_visit)
        //     ->where(function ($query) use ($appTime, $endTime) {
        //         // Check if the new appointment starts during an existing appointment
        //         $query->where(function ($query) use ($appTime) {
        //             $query->whereTime('start_time', '<=', $appTime)
        //                 ->whereTime('end_time', '>', $appTime);
        //         })
        //             // Check if the new appointment ends during an existing appointment
        //             ->orWhere(function ($query) use ($endTime) {
        //                 $query->whereTime('start_time', '<', $endTime)
        //                     ->whereTime('end_time', '>=', $endTime);
        //             })
        //             // Check if the new appointment completely overlaps an existing appointment
        //             ->orWhere(function ($query) use ($appTime, $endTime) {
        //                 $query->whereTime('start_time', '>=', $appTime)
        //                     ->whereTime('end_time', '<=', $endTime);
        //             });
        //     })
        //     ->get();


        // // todo::HERE
        // //check for operator Absence
        // $absence_of_operators = Absence::whereIn('user_id', array_keys($this->fetchData['operators']))
        //     ->whereDate('start_at', '<=', $selected_date_visit)
        //     ->whereDate('end_at', '>=', $selected_date_visit)
        //     ->pluck('user_id')
        //     ->toArray();

        // $existing_operators = [];
        // if ($this->fetchData['appointmentUser_with_operator']->isNotEmpty()) {
        //     foreach ($this->fetchData['appointmentUser_with_operator'] as $appointmentUser) {
        //         $existing_operators[] = $appointmentUser->operator_id;
        //     }
        // }
        // // todo::HERE


        // // Merge existing operators with absent operators
        // $all_existing_or_absent_operators = array_merge($existing_operators, $absence_of_operators);

        // if (!empty($all_existing_or_absent_operators)) {

        //     $operatorsToDeleteFlipped = array_flip($all_existing_or_absent_operators);
        //     $filteredOperators = array_diff_key($this->fetchData['operators'], $operatorsToDeleteFlipped);
        //     return $this->fetchData['operators'] = $filteredOperators;
        // }
        return $this->fetchData['operators'] = User::operators()->mapWithKeys(fn($item) => [$item->id => $item->fullName])->toArray();
    }

    public function mount()
    {
        if (isset($this->appId)) {
            $app =  AppointmentSetting::find($this->appId);
            $this->fetchData['app_kind'] =
                [
                    'online'    => isset($app->detail[AppointmentSetting::VISIT_TYPE_INPERSON]) ? $app->detail[AppointmentSetting::VISIT_TYPE_INPERSON] : false,
                    'in_person' => isset($app->detail[AppointmentSetting::VISIT_TYPE_ONLINE])   ? $app->detail[AppointmentSetting::VISIT_TYPE_ONLINE]   : false,
                ];
            if ($app->detail[AppointmentSetting::VISIT_TYPE_ONLINE] || $app->detail[AppointmentSetting::VISIT_TYPE_INPERSON]) {
                if ($app->detail[AppointmentSetting::VISIT_TYPE_INPERSON]) {
                    $this->form['kind'] = AppointmentUserKindEnum::IN_PERSION;
                } else {
                    $this->form['kind'] = AppointmentUserKindEnum::ONLINE;
                }
            }
            $this->fetchData['app'] = $app;
        }
        if (isset($this->appTime) && !empty($this->appTime)) {
            $this->form['time']['from'] = $this->appTime;
            $this->form['time']['until'] = Carbon::createFromTimeString($this->appTime)->copy()->addMinutes($app->time_for_visit)->toTimeString();
            if ($this->segmentId != null) {
                $segmentItemId = explode(',', $this->segmentId);
                $segments =  $app->segments->first()->items->whereIn('id', $segmentItemId);
                if (count($segments) > 1) {
                    $this->fetchData['segment_time'] = 0;
                    foreach ($segments as $eachSegTime) {
                        $this->fetchData['segment_time'] += $eachSegTime->time;
                    }
                } else {
                    $this->fetchData['segment_time'] = $segments->first()->time;
                }
                $this->form['time']['until'] = Carbon::createFromTimeString($this->appTime)->copy()->addMinutes($this->fetchData['segment_time'])->toTimeString();
            }
        }
        if (isset($this->serviceId)) {
            $this->fetchData['service'] = Service::find($this->serviceId);
        }
        if (setting(SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT)) {
            $this->form['registerWithoutPayment'] = true;
        }
        if ($app->detail[AppointmentSetting::OPERATORS][AppointmentSetting::STATUS] == true) {
            foreach ($app->detail[AppointmentSetting::OPERATORS][AppointmentSetting::IDS] as $key => $user_id) {
                $userName  = User::find($user_id)?->fullName ?? null;
                $userid    =  User::find($user_id)?->id     ?? null;
                if (!empty($userid)) {
                    $this->fetchData['operators'][$userid] = $userName;
                }
            }
        }
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.modal.specific-day-appointment-registration-modal');
    }
}
