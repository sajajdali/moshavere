<?php

return [
    'name' => 'AppointmentSetting',
    'permission' => [
        [
            'gate' => [
                'AppointmentSetting' => 'دسترسی به تنظیمات حضور',
            ],
            'type' => 'light',
            'display_name' => 'تنظیمات زمان های حضور',
            'permissions' => [
                'AppointmentSetting.update' => 'مدیریت تنظیمات',
            ],
        ],
    ],

    'menu' => [
        'title' => 'نوبت دهی',
        'gate' => ['appointment_user','appointment_user.own','AppointmentSetting','absence','segment','appointment_user.feedBack'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 80,
        'children' => [ //it is required
            [
                'title' => 'لیست نوبت های ثبت شده',
                'gate' => ['appointment_user.list','appointment_user.own'],
                'policy_class' => null,
                'icon' => 'fe fe-bar-chart-2',
                'route' => 'admin.appointment_user.list',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'نوبت های آنلاین(در انتظار)',
                'gate' => ['appointment_user.online','appointment_user.own'],
                'policy_class' => null,
                'icon' => 'fe fe-wifi',
                'route' => 'admin.appointment_user.list',
                'param' => '?search[kind]=2&search[AppointmentStatus]=0',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'تنظیمات نوبت دهی',
                'gate' => 'viewAny',
                'policy_class' => \Modules\AppointmentSetting\app\Models\AppointmentSetting::class,
                'icon' => 'fe fe-sliders',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'تنظیمات عمومی',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\AppointmentSetting\app\Models\AppointmentSetting::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.doctor.list',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'بخش بندی نوبت (نواحی بدن)',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\AppointmentSetting\app\Models\AppointmentSegment::class,
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
                'policy_class' => \Modules\Absence\app\Models\Absence::class,
                'icon' => 'fe fe-slash',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'افزودن',
                        'gate' => 'create',
                        'policy_class' => \Modules\Absence\app\Models\Absence::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.absence.create',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\Absence\app\Models\Absence::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.absence.list',
                        'has_child' => false,
                        'children' => null,
                    ],

                ],
            ],
            [
                'title' => 'ثبت نوبت جدید',
                'gate' => 'create',
                'policy_class' => \Modules\AppointmentUser\app\Models\AppointmentUser::class,
                'icon' => 'fe fe-plus-circle',
                'route' => 'admin.appointment_user.addApp',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'پیام های نوبت آنلاین',
                'gate' => 'appointment_user.message',
                'policy_class' => null,
                'icon' => 'fe fe-message-square',
                'route' => 'admin.appointment_user.message.list',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
            [
                'title' => 'نظر سنجی ',
                'gate' => 'appointment_user.feedback',
                'policy_class' => null,
                'icon' => 'fe fe-help-circle',
                'route' => 'admin.appointment.feedback',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
        ],
    ],
];
