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
        ]);
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
