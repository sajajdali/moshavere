<?php

namespace Modules\Setting\Enum;

/**
 * ساختار نوبت دهی سایت.
 * بر اساس این مقدار، ظاهر صفحه اصلی در سمت کاربر انتخاب میشود.
 */
enum AppointmentModeEnum: string
{
    case SINGLE_DOCTOR = 'single_doctor';
    case CLINIC = 'clinic';
    case SINGLE_DOCTOR_WITH_DOCTORS = 'single_doctor_with_doctors';

    public function getName(): string
    {
        return match ($this) {
            self::SINGLE_DOCTOR => 'تک پزشک',
            self::CLINIC => 'نسخه کلینیکی',
            self::SINGLE_DOCTOR_WITH_DOCTORS => 'تک پزشک به همراه چند پزشک',
        };
    }

    /**
     * تصاویری که برای این حالت در تنظیمات نمایش داده میشود
     *
     * @return array<int, SettingKeyEnum>
     */
    public function imageSettings(): array
    {
        return match ($this) {
            self::SINGLE_DOCTOR => [
                SettingKeyEnum::NEW_TPL_DOCTOR_AVATAR,
            ],
            self::CLINIC => [
                SettingKeyEnum::NEW_TPL_HERO_IMAGE,
                SettingKeyEnum::NEW_TPL_CLINIC_LOGO,
                SettingKeyEnum::NEW_TPL_DEPARTMENT_IMAGE,
            ],
            self::SINGLE_DOCTOR_WITH_DOCTORS => [
                SettingKeyEnum::NEW_TPL_HERO_IMAGE,
                SettingKeyEnum::NEW_TPL_DOCTOR_AVATAR,
                SettingKeyEnum::NEW_TPL_CLINIC_LOGO,
                SettingKeyEnum::NEW_TPL_TEAM_IMAGE,
                SettingKeyEnum::NEW_TPL_DEVICE_IMAGE,
                SettingKeyEnum::NEW_TPL_ABOUT_IMAGE,
            ],
        };
    }

    /**
     * فیلدهای محتوایی صفحهٔ اصلی برای هر ساختار نمایش.
     * آدرس، موقعیت و ساعت کاری از اطلاعات عملیاتی مطب خوانده می‌شوند.
     *
     * @return array<int, SettingKeyEnum>
     */
    public function homePageSettings(): array
    {
        $about = [
            SettingKeyEnum::SITE_SECEND_SECTION_TITLE,
            SettingKeyEnum::SITE_SECEND_SECTION_DESCRIPTION,
        ];

        return match ($this) {
            self::SINGLE_DOCTOR => [
                SettingKeyEnum::NEW_TPL_PRIMARY_DOCTOR,
                SettingKeyEnum::NEW_TPL_HERO_BADGE_TEXT,
                SettingKeyEnum::NEW_TPL_HERO_TITLE,
                SettingKeyEnum::NEW_TPL_HERO_SUBTITLE,
                SettingKeyEnum::NEW_TPL_HERO_DESCRIPTION,
                SettingKeyEnum::NEW_TPL_HERO_PRIMARY_BUTTON_TEXT,
                SettingKeyEnum::NEW_TPL_HERO_SECONDARY_BUTTON_TEXT,
                SettingKeyEnum::NEW_TPL_HERO_PHONE,
                ...$about,
                SettingKeyEnum::NEW_TPL_DOCTOR_EDUCATION,
                SettingKeyEnum::NEW_TPL_DOCTOR_EXPERIENCE,
                SettingKeyEnum::NEW_TPL_PATIENTS_COUNT,
            ],
            self::CLINIC => [
                SettingKeyEnum::NEW_TPL_HERO_TITLE,
                SettingKeyEnum::NEW_TPL_HERO_DESCRIPTION,
                ...$about,
            ],
            self::SINGLE_DOCTOR_WITH_DOCTORS => [
                SettingKeyEnum::NEW_TPL_PRIMARY_DOCTOR,
                SettingKeyEnum::NEW_TPL_HERO_TITLE,
                SettingKeyEnum::NEW_TPL_HERO_DESCRIPTION,
                ...$about,
                SettingKeyEnum::NEW_TPL_DEVICES_DESCRIPTION,
            ],
        };
    }

    /**
     * حالت فعال نوبت دهی. در صورت تنظیم نبودن، تک پزشک در نظر گرفته میشود.
     */
    public static function current(): self
    {
        return self::tryFrom((string) setting(SettingKeyEnum::APPOINTMENT_MODE))
            ?? self::SINGLE_DOCTOR;
    }

    /**
     * آیا قالب جدید فعال است.
     * از helper مربوطه استفاده میشود تا پیش از مقداردهی تننت (مثلاً هنگام ثبت
     * مسیرها یا در دستورات کنسول) خطا ندهد و false برگردد.
     */
    public static function newTemplateEnabled(): bool
    {
        return filter_var(setting(SettingKeyEnum::USE_NEW_TEMPLATE), FILTER_VALIDATE_BOOL);
    }

    /**
     * تصاویر قالبی که باید در تنظیمات نمایش داده شوند.
     * وقتی قالب جدید فعال نباشد، هیچ تصویری لازم نیست.
     *
     * @return array<int, SettingKeyEnum>
     */
    public static function currentTemplateImageSettings(): array
    {
        if (! self::newTemplateEnabled()) {
            return [];
        }

        return self::current()->imageSettings();
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getName();
        }

        return $options;
    }
}
