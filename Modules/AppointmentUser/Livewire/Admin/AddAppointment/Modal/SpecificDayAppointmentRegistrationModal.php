<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
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
        "national_code" => null,
        "birth_date" => null,
        "father_name" => null,
        "marital_status" => null,
        "acquaintance" => null,
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
    public string $patientSearch = '';

    /** فیلدهای پرونده مراجع که پیش از ثبت نوبت باید کامل باشند. */
    private const PROFILE_FIELDS = ['national_code', 'birth_date', 'father_name', 'marital_status', 'acquaintance'];

    public const MARITAL_STATUSES = [
        'single' => 'مجرد',
        'married' => 'متأهل',
        'divorced' => 'مطلقه / جدا شده',
        'widowed' => 'همسر فوت شده',
    ];

    public const ACQUAINTANCE_OPTIONS = [
        'اینستاگرام',
        'جستجوی گوگل',
        'معرفی دوستان و آشنایان',
        'معرفی پزشک یا مرکز دیگر',
        'تبلیغات محیطی',
        'سایر',
    ];

    /**
     * Existing patients whose first or last name contains every typed word.
     */
    #[Computed]
    public function patientResults(): array
    {
        $words = array_values(array_filter(preg_split('/\s+/u', trim($this->patientSearch)) ?: []));
        if ($words === [] || mb_strlen(trim($this->patientSearch)) < 2) {
            return [];
        }

        $query = User::query();
        foreach ($words as $word) {
            $like = '%'.addcslashes($word, '%_\\').'%';
            $query->whereHas('metas', function ($q) use ($like) {
                $q->whereIn('meta_key', [UserMetaEnum::FIRST_NAME, UserMetaEnum::LAST_NAME])
                    ->where('meta_value', 'like', $like);
            });
        }

        return $query->limit(8)->get()->map(fn (User $user) => [
            'mobile' => $user->mobile,
            'name' => trim($this->loadedMetaValue($user, UserMetaEnum::FIRST_NAME).' '.$this->loadedMetaValue($user, UserMetaEnum::LAST_NAME)),
        ])->all();
    }

    public function selectPatient(string $mobile)
    {
        $this->form['number'] = $mobile;
        $this->patientSearch = '';
        $this->findeOrCreateUser();
    }

    private function defaultForm(): array
    {
        return [
            'number' => null,
            'document' => null,
            'document_number' => null,
            'first_name' => null,
            'last_name' => null,
            'national_code' => null,
            'birth_date' => null,
            'father_name' => null,
            'marital_status' => null,
            'acquaintance' => null,
            'appType' => 'main_app',
            'smsType' => 'send',
            'kind' => count($this->fetchData['appointment_kinds'] ?? []) === 1
                ? $this->fetchData['appointment_kinds'][0]['value']
                : null,
            'payment_registration' => null,
        ];
    }

    public function dismisModal()
    {
        $this->form = $this->defaultForm();
        $this->patientSearch = '';
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
        foreach (self::PROFILE_FIELDS as $field) {
            $this->form[$field] = null;
        }
        $this->form['document'] = null;
        $this->form['document_number'] = null;
        $this->step = 1;
    }
    public function numberSet()
    {

        if ($this->step == 1) {
            $this->findeOrCreateUser();
        } elseif ($this->step == 2) {
            $this->normalizeProfileInputs();
            $rules = [
                'form.first_name' => 'required',
                'form.last_name' => 'required',
                'form.national_code' => ['required', 'digits:10', Rule::unique('users', 'national_code')->ignore($this->fetchData['user']->id ?? null)],
                'form.birth_date' => ['required', function ($attribute, $value, $fail) {
                    if ($this->parseBirthDate((string) $value) === null) {
                        $fail('تاریخ تولد را به صورت شمسی و صحیح وارد کنید (مثلاً 1370/05/21).');
                    }
                }],
                'form.father_name' => 'required|string|max:100',
                'form.marital_status' => 'required|in:'.implode(',', array_keys(self::MARITAL_STATUSES)),
                'form.acquaintance' => 'required|in:'.implode(',', self::ACQUAINTANCE_OPTIONS),
                'form.time.from' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
                'form.time.until' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            ];

            if (count($this->fetchData['appointment_kinds'] ?? []) > 1) {
                $rules['form.kind'] = 'required|in:'.implode(',', array_column($this->fetchData['appointment_kinds'], 'value'));
            }
            if ($this->paymentEnabledForSelectedKind()) {
                $rules['form.payment_registration'] = 'required|in:confirmed,payment_link';
            }

            $this->validate($rules);

            if ($this->paymentEnabledForSelectedKind()
                && ($this->form['payment_registration'] ?? null) === 'payment_link'
                && blank(setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT))) {
                return $this->addError('form.payment_registration', 'قالب پیامک ارسال لینک پرداخت تعریف نشده است.');
            }

            if (!isset($this->fetchData['user'])) {
                $this->createUser();
            } else {
                $this->saveProfile($this->fetchData['user']);
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
        $this->saveProfile($this->fetchData['user']);
        if (isset($this->form['document_number'])) {
            $this->fetchData['user']->document_number = $this->form['document_number'];
        }
    }

    /**
     * اطلاعات پرونده مراجع (مشخصات هویتی، تاریخ تولد، نام پدر، تاهل و نحوه آشنایی) را روی کاربر ذخیره می کند.
     */
    private function saveProfile(User $user): void
    {
        $birth = $this->parseBirthDate((string) $this->form['birth_date']);

        $user->first_name = $this->form['first_name'];
        $user->last_name = $this->form['last_name'];
        $user->national_code = $this->form['national_code'];
        $user->birthday = json_encode(['year' => $birth['year'], 'month' => $birth['month'], 'day' => $birth['day']]);
        $user->father_name = trim((string) $this->form['father_name']);
        $user->marital_status = $this->form['marital_status'];
        $user->acquaintance = $this->form['acquaintance'];
    }

    private function normalizeProfileInputs(): void
    {
        foreach (['national_code', 'birth_date'] as $field) {
            if (filled($this->form[$field] ?? null)) {
                $this->form[$field] = (string) convert2english(trim((string) $this->form[$field]));
            }
        }
        if (filled($this->form['father_name'] ?? null)) {
            $this->form['father_name'] = trim((string) $this->form['father_name']);
        }
    }

    /**
     * @return array{year:int,month:int,day:int}|null
     */
    private function parseBirthDate(string $value): ?array
    {
        $value = (string) convert2english(trim($value));
        if (!preg_match('#^(\d{4})[/-](\d{1,2})[/-](\d{1,2})$#', $value, $m)) {
            return null;
        }
        [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        $today = Verta::now();
        if ($year < 1300 || $month < 1 || $month > 12 || $day < 1 || $day > ($month <= 6 ? 31 : 30)) {
            return null;
        }
        if ($year > $today->year || ($year === $today->year && ($month > $today->month || ($month === $today->month && $day > $today->day)))) {
            return null;
        }

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }

    /** سن محاسبه شده از تاریخ تولد واردشده، برای نمایش در فرم. */
    public function birthAge(): ?int
    {
        $birth = $this->parseBirthDate((string) ($this->form['birth_date'] ?? ''));
        if ($birth === null) {
            return null;
        }
        $today = Verta::now();
        $age = $today->year - $birth['year'];
        if ($today->month < $birth['month'] || ($today->month === $birth['month'] && $today->day < $birth['day'])) {
            $age--;
        }

        return max($age, 0);
    }
    private function findeOrCreateUser()
    {
        if (filled($this->form['number'] ?? null)) {
            $this->form['number'] = self::normalizeMobileNumber((string) $this->form['number']);
        }

        $this->validate([
            'form.number' => ['required', 'digits:11'],
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
            $this->form['national_code'] = $user->national_code;
            $this->form['father_name'] = $this->loadedMetaValue($user, UserMetaEnum::FATHER_NAME);
            $this->form['marital_status'] = $this->loadedMetaValue($user, UserMetaEnum::MARITAL_STATUS);
            $this->form['acquaintance'] = $this->loadedMetaValue($user, UserMetaEnum::ACQUAINTANCE);

            $birth = json_decode((string) $this->loadedMetaValue($user, UserMetaEnum::BIRTHDAY), true);
            $this->form['birth_date'] = is_array($birth) && isset($birth['year'], $birth['month'], $birth['day'])
                ? sprintf('%04d/%02d/%02d', $birth['year'], $birth['month'], $birth['day'])
                : null;
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
            $appSetting,
            $this->serviceId ?? $this->fetchData['service']?->id,
            $this->placeId
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
        $user = $this->fetchData['user'];
        $appointmentSetting = AppointmentSetting::findOrFail($this->appId);
        $availableKinds = self::resolveAvailableKinds($appointmentSetting->detail ?? []);
        $kind = count($availableKinds) === 1
            ? $availableKinds[0]
            : AppointmentUserKindEnum::tryFrom((int) ($this->form['kind'] ?? 0));

        if (!$kind || !in_array($kind, $availableKinds, true)) {
            return $this->addError('form.kind', 'لطفاً نوع نوبت را انتخاب کنید.');
        }

        $paymentEnabled = self::isPaymentEnabledForKind($appointmentSetting->detail ?? [], $kind);
        if ($paymentEnabled && !in_array($this->form['payment_registration'] ?? null, ['confirmed', 'payment_link'], true)) {
            return $this->addError('form.payment_registration', 'نحوه ثبت نوبت را انتخاب کنید.');
        }
        if ($paymentEnabled && ($this->form['payment_registration'] ?? null) === 'payment_link'
            && blank(setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT))) {
            return $this->addError('form.payment_registration', 'قالب پیامک ارسال لینک پرداخت تعریف نشده است.');
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
        if ($paymentEnabled && ($this->form['payment_registration'] ?? null) === 'payment_link') {
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
            'form.number.required' => 'وارد کردن شماره موبایل الزامی است.',
            'form.number.digits' => 'شماره موبایل صحیح نیست!',
            'form.first_name.required' => 'وارد کردن نام الزامی است',
            'form.last_name.required' => 'وارد کردن نام خانوادگی الزامی است',
            'form.national_code.required' => 'وارد کردن کد ملی الزامی است',
            'form.national_code.digits' => 'کد ملی باید ۱۰ رقم باشد',
            'form.national_code.unique' => 'این کد ملی قبلاً برای مراجع دیگری ثبت شده است',
            'form.birth_date.required' => 'وارد کردن تاریخ تولد الزامی است',
            'form.father_name.required' => 'وارد کردن نام پدر الزامی است',
            'form.father_name.max' => 'نام پدر حداکثر ۱۰۰ کاراکتر باشد',
            'form.marital_status.required' => 'وضعیت تأهل را انتخاب کنید',
            'form.marital_status.in' => 'وضعیت تأهل انتخاب‌شده معتبر نیست',
            'form.acquaintance.required' => 'نحوه آشنایی با مرکز را انتخاب کنید',
            'form.acquaintance.in' => 'نحوه آشنایی انتخاب‌شده معتبر نیست',
            'form.time.from.required' => 'زمان نوبت به درستی انتخاب نشده است!',
            'form.time.until.required' => 'زمان پایان نوبت به درستی انتخاب نشده است!',
            'form.time.from.regex' => 'زمان نوبت را به صورت ۲۴ ساعته (مثلاً 09:30) وارد کنید.',
            'form.time.until.regex' => 'زمان پایان نوبت را به صورت ۲۴ ساعته (مثلاً 17:30) وارد کنید.',
            'form.kind.required' => 'لطفاً نوع نوبت را انتخاب کنید.',
            'form.kind.in' => 'نوع نوبت انتخاب‌شده معتبر نیست.',
            'form.payment_registration.required' => 'نحوه ثبت نوبت را انتخاب کنید.',
            'form.payment_registration.in' => 'نحوه ثبت نوبت معتبر نیست.',
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

    public static function isPaymentEnabledForKind(array $detail, AppointmentUserKindEnum|int|string|null $kind): bool
    {
        if (! $kind instanceof AppointmentUserKindEnum) {
            $kind = AppointmentUserKindEnum::tryFrom((int) $kind);
        }
        if (! $kind || !filter_var(data_get($detail, AppointmentSetting::PAYMENT.'.'.AppointmentSetting::STATUS, false), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $paymentType = match ($kind) {
            AppointmentUserKindEnum::IN_PERSION => AppointmentSetting::IN_PERSON,
            AppointmentUserKindEnum::VOIP => AppointmentSetting::VOIP,
            AppointmentUserKindEnum::ONLINE => AppointmentSetting::ONLINE,
        };

        return filter_var(data_get($detail, AppointmentSetting::PAYMENT.'.'.$paymentType.'.'.AppointmentSetting::STATUS, false), FILTER_VALIDATE_BOOLEAN);
    }

    public function paymentEnabledForSelectedKind(): bool
    {
        return self::isPaymentEnabledForKind(
            data_get($this->fetchData, 'app.detail', []),
            $this->form['kind'] ?? null,
        );
    }

    public function mount()
    {
        $this->fetchData['document_number_enabled'] = (bool) setting(SettingKeyEnum::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER);
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
