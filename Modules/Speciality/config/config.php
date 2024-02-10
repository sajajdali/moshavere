<?php

return [
    'name' => 'Speciality',
    'permission' => [
        [
            'gate' => [
                'speciality' => 'مدیریت تخصص ها',
            ],
            'type' => 'success',
            'display_name' => 'تخصص ها',
            'permissions' => [
                'speciality.create' => 'ایجاد تخصص',
                'speciality.edit' => 'ویرایش تخصص',
                'speciality.delete' => 'حذف تخصص',
            ],
        ],
    ],

    'menu' => [
        'title' => 'تعاریف پایه',
        'gate' => ['speciality'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 90,
        'children' => [ //it is required
            [
                'title' => 'تخصص ها',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-paperclip',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.speciality.index',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'افزودن',
                        'gate' => 'create',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-plus-circle',
                        'route' => 'admin.speciality.manage',
                        'has_child' => false,
                        'children' => null,
                    ],
                ],
            ],
            [
                'title' => 'مدیریت مطب',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-map-pin',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'اضافه کردن',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.place.create',
                        'has_child' => false,
                        'children' => null,
                    ],

                ],
            ],
            [
                'title' => 'بخش ها',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-anchor',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => '',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'افزودن',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-plus-circle',
                        'route' => 'admin.service.create',
                        'has_child' => false,
                        'children' => null,
                    ],
                ],
            ],
        ],
    ],
];
