<?php

return [
    'name' => 'Admin',
    'permission' => [
        [
            'gate' => [
                'admin.dashboard' => 'پیشخوان مدیریت',
            ],
            'type' => 'light',
            'display_name' => 'پیشخوان مدیریت',
            'permissions' => [
                'admin.dashboard.appointments' => 'مشاهده نوبت ها',
                'admin.dashboard.payment'      => 'مشاهده پرداختی ها',
                'admin.dashboard.analytic'     => 'مشاهده امار نوبت ها',
            ],
        ],
    ],

    'menu' => [
        'title' => 'مدیریت',
        'gate' => 'ADMIN_ACCESS',
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 110,
        'children' => [//it is required
            [
                'title' => 'پیشخوان',
                'gate' => 'ADMIN_ACCESS',
                'policy_class' => null,
                'icon' => 'fe fe-home',
                'route' => 'admin.dashboard',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
            // [
            //     'title' => 'فایل‌ها',
            //     'gate' => 'ADMIN_ACCESS',
            //     'policy_class' => null,
            //     'icon' => 'fe fe-folder',
            //     'route' => 'admin.file',
            //     'has_badge' => false,
            //     'has_child' => false,
            //     'children' => null,
            // ],
            [
                'title' => 'تماس‌های ورودی',
                'gate' => 'ADMIN_ACCESS',
                'policy_class' => null,
                'setting_key' => \Modules\Setting\Enum\SettingKeyEnum::VOIP_APPOINTMENT_STATUS,
                'icon' => 'fe fe-phone-incoming',
                'route' => 'admin.incoming-calls',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
            [
                'title' => 'پیغام های ضبط شده',
                'gate' => 'ADMIN_ACCESS',
                'policy_class' => null,
                'setting_key' => \Modules\Setting\Enum\SettingKeyEnum::VOIP_VOICE_RECORD_STATUS,
                'icon' => 'fe fe-mic',
                'route' => 'admin.voice-records',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
        ],
    ],
];
