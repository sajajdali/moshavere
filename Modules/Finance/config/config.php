<?php

return [
    'name' => 'Finance',
    'permission' => [
        [
            'gate' => [
                'finance' => 'مدیریت مالی (مشاهده پرداخت ها و گزارش ها)',
            ],
            'type' => 'light',
            'display_name' => 'مدیریت مالی',
            'permissions' => [
                'finance.create' => 'ثبت پرداخت دستی',
                'finance.edit' => 'ویرایش پرداخت دستی',
                'finance.delete' => 'حذف پرداخت دستی',
                'finance.purposes' => 'مدیریت دلایل پرداخت',
                'finance.export' => 'خروجی اکسل گزارش های مالی',
            ],
        ],
    ],
    'menu' => [
        'title' => 'مدیریت مالی',
        'gate' => 'finance',
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 72,
        'children' => [
            [
                'title' => 'داشبورد مالی',
                'gate' => 'finance',
                'policy_class' => null,
                'icon' => 'fe fe-pie-chart',
                'route' => 'admin.finance.dashboard',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
            [
                'title' => 'پرداخت ها',
                'gate' => 'finance',
                'policy_class' => null,
                'icon' => 'fe fe-credit-card',
                'route' => 'admin.finance.payments',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
            [
                'title' => 'گزارش مالی بیماران',
                'gate' => 'finance',
                'policy_class' => null,
                'icon' => 'fe fe-users',
                'route' => 'admin.finance.patients',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
            [
                'title' => 'دلایل پرداخت',
                'gate' => 'finance.purposes',
                'policy_class' => null,
                'icon' => 'fe fe-list',
                'route' => 'admin.finance.purposes',
                'has_badge' => false,
                'has_child' => false,
                'children' => null,
            ],
        ],
    ],
];
