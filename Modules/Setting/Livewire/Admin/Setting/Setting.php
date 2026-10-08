<?php

namespace Modules\Setting\Livewire\Admin\Setting;

use App\trait\UploadFile;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Setting\Enum\AppointmentModeEnum;
use Modules\Setting\Enum\SettingKeyEnum;

#[title('تنظیمات سایت')]
class Setting extends Component
{
    #[Url]
    public string $section = 'sms';

    public ?array $options = null;

    public ?array $menuSections = null;

    public array $settingValues = [];


    #[On('settingUpdateListener')]
    public function settingUpdateListener(int $settingKey, mixed $value): void
    {
        $this->settingValues[$settingKey] = $value;
    }

    public function changeMenu(?string $menuId): void
    {
        if ($this->section === $menuId && $this->options !== null) {
            return;
        }
        $setting = \Modules\Setting\Entities\Setting::getSettingSections();
        $this->settingValues = [];
        $this->section = $menuId;
        foreach ($setting as $key => $menu) {
            if ($key === $this->section && $this->sectionIsVisible($menu)) {
                $this->options = $this->visibleSettings($this->sectionSettings($menu));
                foreach ($this->options as $tmpSet) {
                    $this->settingValues[$tmpSet->value] = \Modules\Setting\Entities\Setting::getOriginalVal($tmpSet->value);
                }
            }
        }
    }

    public function storeSetting()
    {
        foreach ([
            SettingKeyEnum::CONSULT_SMS_PATIENT_FIRST_MINUTES,
            SettingKeyEnum::CONSULT_SMS_PATIENT_SECOND_MINUTES,
            SettingKeyEnum::CONSULT_SMS_PATIENT_FINAL_MINUTES,
            SettingKeyEnum::CONSULT_SMS_PRACTITIONER_REMINDER_MINUTES,
        ] as $key) {
            $value = $this->settingValues[$key->value] ?? null;
            if ($value !== null && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10080]]) === false) {
                $this->addError('settingValues.'.$key->value, $key->getName().' باید عدد صحیح باشد.');
                return;
            }
        }
        $dailyTime = $this->settingValues[SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TIME->value] ?? null;
        if ($dailyTime !== null && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $dailyTime) !== 1) {
            $this->addError('settingValues.'.SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TIME->value, 'ساعت ارسال برنامه فردا باید با قالب HH:MM وارد شود.');
            return;
        }
        foreach ($this->settingValues as $key => $value) {
            if ($this->isRestrictedSetting((int) $key) && auth()->id() !== 1) {
                continue;
            }

            \Modules\Setting\Entities\Setting::setVal((int) $key, $value);
        }
        \Modules\Setting\Entities\Setting::reBuild();
        $this->dispatch('success-saving', message: 'تنظیمات با موفقیت ذخیره شدند');
    }

    public function mount()
    {
        $this->changeMenu($this->section);
        $setting = \Modules\Setting\Entities\Setting::getSettingSections();
        $this->menuSections = [];
        foreach ($setting as $key => $menu) {
            if (! $this->sectionIsVisible($menu)) {
                continue;
            }

            $this->menuSections[] = [
                'id' => $key,
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'disable_ui' => $menu['disable_ui'] ?? false,
            ];
        }
    }

    public function render()
    {
        $setting = \Modules\Setting\Entities\Setting::getSettingSections();
        $this->options = [];
        foreach ($setting as $key => $menu) {
            if ($key === $this->section && $this->sectionIsVisible($menu)) {
                $this->options = $this->visibleSettings($this->sectionSettings($menu));
                // تنظیماتی که تازه به فرم اضافه شده اند (مثل تصاویر قالب) مقدار فعلی خود را بگیرند
                foreach ($this->options as $tmpSet) {
                    if (! array_key_exists($tmpSet->value, $this->settingValues)) {
                        $this->settingValues[$tmpSet->value] = \Modules\Setting\Entities\Setting::getOriginalVal($tmpSet->value);
                    }
                }
            }
        }
        return view('setting::livewire.admin.setting.setting');
    }

    private function visibleSettings(array $settings): array
    {
        return array_values(array_filter(
            $settings,
            fn (SettingKeyEnum $setting): bool => ($setting !== SettingKeyEnum::VOIP_SERVER_ADDRESS
                || (class_exists(\Modules\OnlineConsultation\Support\ConsultationAccess::class)
                    && \Modules\OnlineConsultation\Support\ConsultationAccess::enabled()))
                && (! $this->isRestrictedSetting($setting->value)
                || auth()->id() === 1)
                && $this->settingIsVisible($setting),
        ));
    }

    /**
     * انتخاب پزشک اصلی فقط زمانی نمایش داده میشود که ساختار سایت «تک پزشک»
     * باشد و بیش از یک پزشک با نوبت دهی فعال در سیستم ثبت شده باشد.
     */
    private function settingIsVisible(SettingKeyEnum $setting): bool
    {
        if ($setting !== SettingKeyEnum::NEW_TPL_PRIMARY_DOCTOR) {
            return true;
        }

        $mode = $this->settingValues[SettingKeyEnum::APPOINTMENT_MODE->value] ?? null;
        $mode = AppointmentModeEnum::tryFrom((string) $mode) ?? AppointmentModeEnum::current();

        if ($mode === AppointmentModeEnum::CLINIC) {
            return false;
        }

        return count(\Modules\User\Entities\User::activeAppointmentDoctorOptions()) > 1;
    }

    private function sectionIsVisible(array $section): bool
    {
        // بخش هایی که فقط به قالب قدیم مربوط اند، با فعال شدن قالب جدید پنهان میشوند
        if (($section['hide_when_new_template'] ?? false) && $this->newTemplateSelected()) {
            return false;
        }

        return ! isset($section['auth_user_id'])
            || auth()->id() === (int) $section['auth_user_id'];
    }

    /**
     * تنظیمات یک بخش. در بخش صفحهٔ اصلی، فیلدهای محتوایی و تصاویر بر اساس
     * ساختار انتخاب‌شده به صورت زنده به فرم اضافه می‌شوند.
     *
     * @return array<int, SettingKeyEnum>
     */
    private function sectionSettings(array $section): array
    {
        $settings = $section['settings'];

        if ($section['dynamic_home_page'] ?? false) {
            if (! $this->newTemplateSelected()) {
                return array_merge($settings, $section['legacy_settings'] ?? []);
            }

            $mode = $this->selectedAppointmentMode();
            $settings = array_merge($settings, $mode->homePageSettings(), $mode->imageSettings());
        }

        if ($section['append_template_images'] ?? false) {
            $settings = array_merge($settings, $this->templateImageSettings());
        }

        return array_values(array_unique($settings, SORT_REGULAR));
    }

    private function selectedAppointmentMode(): AppointmentModeEnum
    {
        $mode = $this->settingValues[SettingKeyEnum::APPOINTMENT_MODE->value] ?? null;

        return AppointmentModeEnum::tryFrom((string) $mode) ?? AppointmentModeEnum::current();
    }

    /**
     * تصاویر قالب جدید بر اساس مقادیر جاری فرم (حتی پیش از ذخیره شدن)،
     * تا با تغییر ساختار یا فعال/غیرفعال کردن قالب، فرم بلافاصله به روز شود.
     *
     * @return array<int, SettingKeyEnum>
     */
    private function templateImageSettings(): array
    {
        if (! $this->newTemplateSelected()) {
            return [];
        }

        return $this->selectedAppointmentMode()->imageSettings();
    }

    /**
     * آیا قالب جدید انتخاب شده است. مقدار ذخیره نشده فرم بر مقدار ذخیره شده اولویت دارد.
     */
    private function newTemplateSelected(): bool
    {
        $pending = $this->settingValues[SettingKeyEnum::USE_NEW_TEMPLATE->value] ?? null;

        if ($pending !== null) {
            return filter_var($pending, FILTER_VALIDATE_BOOL);
        }

        return AppointmentModeEnum::newTemplateEnabled();
    }

    private function isRestrictedSetting(int $settingKey): bool
    {
        return in_array($settingKey, [
            SettingKeyEnum::DISABLE_ONLINE_APPOINTMENT->value,
            SettingKeyEnum::DISABLE_UI_FOR_VOIP_ONLY_APPOINTMENT->value,
            SettingKeyEnum::VOIP_APPOINTMENT_STATUS->value,
        ], true);
    }
}
