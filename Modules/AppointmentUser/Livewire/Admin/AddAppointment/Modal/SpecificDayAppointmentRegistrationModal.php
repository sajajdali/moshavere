<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class SpecificDayAppointmentRegistrationModal extends Component
{
    public array $form = [
        "number" => null,
        "document" => null,
        "first_name" => null,
        "last_name" => null,
        "appType" => 'main_app',
        "smsType" => 'send',
        "kind" => null,
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

    private function defaultForm(): array
    {
        return [
            'number' => null,
            'document' => null,
            'document_number' => null,
            'first_name' => null,
            'last_name' => null,
            'appType' => 'main_app',
            'smsType' => 'send',
            'kind' => count($this->fetchData['appointment_kinds'] ?? []) === 1
                ? $this->fetchData['appointment_kinds'][0]['value']
                : null,
            'registerWithoutPayment' => ($this->fetchData['payment_link_enabled'] ?? false) ? true : null,
        ];
    }

    public function dismisModal()
    {
        $this->form = $this->defaultForm();
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
        if ($this->step === 3) {
            $this->step = 2;

            return;
        }

        if (isset($this->fetchData['user'])) {
            unset($this->fetchData['user']);
        }
        unset($this->fetchData['tempUser']);
        $this->form['first_name'] = null;
        $this->form['last_name'] = null;
        $this->form['document'] = null;
        $this->form['document_number'] = null;
        $this->step = 1;
    }
    public function numberSet()
    {

        if ($this->step == 1) {
            $this->findeOrCreateUser();
        } elseif ($this->step == 2) {
            $rules = [
                'form.first_name' => 'required',
                'form.last_name' => 'required',
                'form.time.from' => 'required',
                'form.time.until' => 'required',
            ];

            if (count($this->fetchData['appointment_kinds'] ?? []) > 1) {
                $rules['form.kind'] = 'required|in:'.implode(',', array_column($this->fetchData['appointment_kinds'], 'value'));
            }

            $this->validate($rules);

            if (!isset($this->fetchData['user'])) {
                $this->createUser();
            }
            if (isset($this->fetchData['operators'])) {
                $this->step = 3;
            } else {
                return $this->storeAppointmentByAdmin();
            }
        } elseif ($this->step == 3) {
            return $this->storeAppointmentByAdmin();
        } elseif ($this->step == 4) {
            return $this->storeApp();
        }
    }

    public static function normalizeMobileNumber(string $number): string
    {
        $number = strtr($number, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return preg_replace('/[^0-9]/', '', $number) ?? '';
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
        if (filled($this->form['number'] ?? null)) {
            $this->form['number'] = self::normalizeMobileNumber((string) $this->form['number']);
        }

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
            $user = $this->fetchData['user'];
            $this->form['document'] = $this->loadedMetaValue($user, UserMetaEnum::DOCUMENT_NUMBER);
            $this->form['document_number'] = $this->form['document'];
            $this->form['first_name'] = $this->loadedMetaValue($user, UserMetaEnum::FIRST_NAME);
            $this->form['last_name'] = $this->loadedMetaValue($user, UserMetaEnum::LAST_NAME);
        }
    }

    private function loadedMetaValue(User $user, UserMetaEnum $metaKey): ?string
    {
        return $user->metas
            ->where('meta_key', $metaKey)
            ->last()
            ?->meta_value;
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
                return $this->addError('form.registerWithoutPayment', 'قالب پیامک ارسال لینک پرداخت تعریف نشده است.');
            }
        }
        $user = $this->fetchData['user'];
        $appointmentSetting = AppointmentSetting::findOrFail($this->appId);
        $availableKinds = self::resolveAvailableKinds($appointmentSetting->detail ?? []);
        $kind = count($availableKinds) === 1
            ? $availableKinds[0]
            : AppointmentUserKindEnum::tryFrom((int) ($this->form['kind'] ?? 0));

        if (!$kind || !in_array($kind, $availableKinds, true)) {
            return $this->addError('form.kind', 'لطفاً نوع نوبت را انتخاب کنید.');
        }

        // If he wants to take the appointmnet for someone else
        $someoneModel = null;
        $foHimself = 1;

        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $this->form['first_name'],
            lastName: $this->form['last_name'],
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
            placeId: $this->placeId,
            agentId: auth()->user()->id,
            operatorId: $oprator,
            kind: $kind,
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
        $this->form = $this->defaultForm();
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
            'form.time.until.required' => 'زمان پایان نوبت به درستی انتخاب نشده است!',
            'form.kind.required' => 'لطفاً نوع نوبت را انتخاب کنید.',
            'form.kind.in' => 'نوع نوبت انتخاب‌شده معتبر نیست.',
        ];
    }

    public static function resolveAvailableKinds(array $detail): array
    {
        $kinds = [];

        if (filter_var(data_get($detail, AppointmentSetting::VISIT_TYPE_INPERSON, false), FILTER_VALIDATE_BOOLEAN)) {
            $kinds[] = AppointmentUserKindEnum::IN_PERSION;
        }
        if (filter_var(data_get($detail, AppointmentSetting::VISIT_TYPE_VOIP, false), FILTER_VALIDATE_BOOLEAN)) {
            $kinds[] = AppointmentUserKindEnum::VOIP;
        }
        if (filter_var(data_get($detail, AppointmentSetting::VISIT_TYPE_ONLINE, false), FILTER_VALIDATE_BOOLEAN)) {
            $kinds[] = AppointmentUserKindEnum::ONLINE;
        }

        // Older appointment settings represented VOIP by leaving both legacy flags off.
        if ($kinds === [] && !array_key_exists(AppointmentSetting::VISIT_TYPE_VOIP, $detail)) {
            $kinds[] = AppointmentUserKindEnum::VOIP;
        }

        return $kinds;
    }

    public function mount()
    {
        $this->fetchData['document_number_enabled'] = (bool) setting(SettingKeyEnum::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER);
        $this->fetchData['payment_link_enabled'] = (bool) setting(SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT);

        if (isset($this->appId)) {
            $app = AppointmentSetting::findOrFail($this->appId);
            $availableKinds = self::resolveAvailableKinds($app->detail ?? []);
            $kindPresentation = [
                AppointmentUserKindEnum::IN_PERSION->value => ['icon' => 'fa-user-md', 'hint' => 'مراجعه به مطب', 'class' => 'in-person'],
                AppointmentUserKindEnum::VOIP->value => ['icon' => 'fa-phone', 'hint' => 'تماس تلفنی (ویپ)', 'class' => 'voip'],
                AppointmentUserKindEnum::ONLINE->value => ['icon' => 'fa-laptop', 'hint' => 'گفت‌وگوی آنلاین', 'class' => 'online'],
            ];
            $this->fetchData['appointment_kinds'] = array_map(
                fn (AppointmentUserKindEnum $kind) => [
                    'value' => $kind->value,
                    'name' => $kind === AppointmentUserKindEnum::VOIP ? 'تلفنی (ویپ)' : $kind->getName(),
                    ...$kindPresentation[$kind->value],
                ],
                $availableKinds
            );
            $this->form['kind'] = count($availableKinds) === 1 ? $availableKinds[0]->value : null;
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
        if ($this->fetchData['payment_link_enabled']) {
            $this->form['registerWithoutPayment'] = true;
        }
        if ((bool) data_get($app->detail, AppointmentSetting::OPERATORS.'.'.AppointmentSetting::STATUS, false)) {
            $operatorIds = array_filter((array) data_get($app->detail, AppointmentSetting::OPERATORS.'.'.AppointmentSetting::IDS, []));
            $operators = User::query()
                ->whereIn('id', $operatorIds)
                ->get()
                ->mapWithKeys(fn (User $user) => [
                    $user->id => trim(
                        $this->loadedMetaValue($user, UserMetaEnum::FIRST_NAME).' '.
                        $this->loadedMetaValue($user, UserMetaEnum::LAST_NAME)
                    ),
                ])
                ->toArray();

            if ($operators !== []) {
                $this->fetchData['operators'] = $operators;
            }
        }
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.modal.specific-day-appointment-registration-modal');
    }
}
