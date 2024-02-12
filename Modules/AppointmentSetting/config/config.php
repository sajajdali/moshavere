<?php

return [
    'name' => 'AppointmentSetting',
    'permission' => [
        [
            'gate' => [
                'speciality' => 'تنظیمات زمان های حضور',
            ],
            'type' => 'success',
            'display_name' => 'تنظیمات عمومی',
            'permissions' => [
                'speciality.update' => 'مدیریت تنظیمات',
            ],
        ],
    ],

    'menu' => [
        'title' => 'تنظیمات',
        'gate' => ['speciality'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 80,
        'children' => [//it is required
            [
                'title' => 'تنظیمات نوبت دهی',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-sliders',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'تنظیمات عمومی',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.doctor.list',
                        'has_child' => false,
                        'children' => null,
                    ],

                ],
            ],
            [
                'title' => 'عدم حضور',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-slash',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'افزودن',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.absentee',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.absentee.list',
                        'has_child' => false,
                        'children' => null,
                    ],

                ],
            ],
        ],
    ],
];
