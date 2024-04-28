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
        'children' => [ //it is required
            [
                'title' => 'لیست نوبت های ثبت شده',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-bar-chart-2',
                'route' => 'admin.appointment_user.list',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
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
                    [
                        'title' => 'بخش بندی نوبت (نواحی بدن)',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.segment.list',
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
                        'route' => 'admin.absence.create',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.absence.list',
                        'has_child' => false,
                        'children' => null,
                    ],

                ],
            ],
            [
                'title' => 'ثبت نوبت جدید',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-plus-circle',
                'route' => 'admin.appointment_user.addApp',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'پیام های نوبت آنلاین',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-message-square',
                'route' => 'admin.appointment_user.message.list',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
        ],
    ],
];
