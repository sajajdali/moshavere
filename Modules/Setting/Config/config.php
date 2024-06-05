<?php

return [
    'name' => 'Setting',
    'permission' => [
        [
            'gate' => [
                'setting' => 'دسترسی به بخش تنظیمات',
            ],
            'type' => 'light',
            'display_name' => 'بخش تنظیمات',
            'permissions' => [
                'faq' => 'دسترسی به سوالات متداول'
            ]
        ],
    ],
    'menu' => [
        'title' => 'تنظیمات',
        'gate' => ['setting'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 10,
        'children' => [//it is required
            [
                'title' => 'تنظیمات',
                'gate' => 'setting',
                'policy_class' => null,
                'icon' => 'fe fe-settings',
                'route' => 'admin.setting',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'سوالات متداول',
                'gate' => 'faq',
                'policy_class' => Modules\Front\app\Models\Faq::class,
                'icon' => 'fe fe-help-circle',
                'route' => 'admin.faq',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
        ],
    ],

    'setting' => require_once __DIR__ . '/setting.php'
];
