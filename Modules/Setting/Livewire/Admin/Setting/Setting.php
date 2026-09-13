<?php

namespace Modules\Setting\Livewire\Admin\Setting;

use App\trait\UploadFile;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
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
                $this->options = $this->visibleSettings($menu['settings']);
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
                $this->options = $this->visibleSettings($menu['settings']);
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
                || auth()->id() === 1),
        ));
    }

    private function sectionIsVisible(array $section): bool
    {
        return ! isset($section['auth_user_id'])
            || auth()->id() === (int) $section['auth_user_id'];
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
