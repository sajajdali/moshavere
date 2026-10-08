<?php

use Modules\Setting\Entities\Setting;

if (! function_exists('front_asset')) {
    function front_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('assets/front/' . $file) . '?v=' . md5($version);
    }
}
if (! function_exists('front_setting_array')) {
    function front_setting_array()
    {
        return  Modules\Setting\Entities\Setting::getSettingByArray([
            Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::SHOW_ABOUT_US_MENU_BUTTON,
            \Modules\Setting\Enum\SettingKeyEnum::SHOW_FLOATING_SOCIAL_ICONS,
            \Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::WHATSAPP_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::DISABLE_UI_FOR_VOIP_ONLY_APPOINTMENT,
            \Modules\Setting\Enum\SettingKeyEnum::DISABLE_FOOTER_DISPLAY,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::TELEGRAM_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_ENAMAD,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_SAMANDEHI,
            \Modules\Setting\Enum\SettingKeyEnum::FAVICON_16,
            \Modules\Setting\Enum\SettingKeyEnum::FAVICON_32,
            \Modules\Setting\Enum\SettingKeyEnum::FAVICON_APPLE_TOUCH,
        ]);
    }
}
// کد نماد اینماد برای نمایش در فوتر
if (! function_exists('enamad_html')) {
    function enamad_html(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // کد کامل اینماد (تگ a به همراه تصویر)؛ سایر تگ ها حذف میشوند
        if (str_contains($value, '<')) {
            $value = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $value);

            return trim(strip_tags($value, '<a><img>')) ?: null;
        }

        // مقادیر قدیمی که فقط لینک اینماد ذخیره شده بود
        return '<a referrerpolicy="origin" target="_blank" href="' . e($value) . '">'
            . '<img referrerpolicy="origin" src="' . front_asset('assets/images/enamad.png') . '" alt="" style="cursor:pointer"></a>';
    }
}
// setting value from collection
if (! function_exists('settingVfc')) {
    function settingVfc($collection, $enum): null |string
    {
        $setting = new Setting();
        if (is_array($collection)) {
            $collection = collect($collection);
        }
        return $setting->vc($collection, $enum);
    }
}
