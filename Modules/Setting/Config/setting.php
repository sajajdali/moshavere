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
