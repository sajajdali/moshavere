<?php

return [
//    'base' => [
//        'title' => 'تنظیمات پایه',
//        'icon' => 'fa fa-gear',
//        'settings' => [
//            \Modules\Setting\Enum\SettingKeyEnum::BASE_TITLE,
//        ],
//    ],
    'exercise' => [
        'title' => 'ورزش‌ها',
        'icon' => 'fa fa-futbol-o',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::DEFAULT_EXERCISE_STATUS
        ],
    ],
    'sms' => [
        'title' => 'پیامک',
        'icon' => 'fa fa-mobile',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_LOGIN_TEMPLATE
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
    'APP' => [
        'title' => 'تنظیمات اپ',
        'icon' => 'fa fa-gear',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::WEIGHT_CHART_DESCRIPTION_APP,
        ],
    ],
];
