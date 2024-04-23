<?php

return [
    'sms' => [
        'title' => 'پیامک',
        'icon' => 'fa fa-mobile',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_LOGIN_TEMPLATE,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_CANCEL,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TO_OPERATOR,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT,

        ],
    ],
    'website' => [
        'title' => 'تنظمات وبسایت',
        'icon' => 'fa fa-globe',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE,

        ],
    ],
    'support' => [
        'title' => 'پشتیبانان',
        'icon' => 'fa fa-user',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SUPPORT_USER_ROLE
        ],
    ],
    'payment' => [
        'title' => 'تنظیمات پرداخت',
        'icon' => 'fa fa-credit-card',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PAYSTAR_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PAYSTAR_SIGN,
        ],
    ],
    'voip' => [
        'title' => 'تنظیمات Voip',
        'icon' => 'fa fa-phone',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::VOIP_USERNAME,
            \Modules\Setting\Enum\SettingKeyEnum::VOIP_PASSWORD,
        ],
    ],
    'APP' => [
        'title' => 'تنظیمات اپ',
        'icon' => 'fa fa-gear',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::WEIGHT_CHART_DESCRIPTION_APP,
        ],
    ],
];
