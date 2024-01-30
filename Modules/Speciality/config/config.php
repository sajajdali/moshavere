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
        'title' => 'تخصص ها',
        'gate' => ['speciality'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 110,
        'children' => [//it is required
            [
                'title' => 'کاربران',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-user',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.user.index',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'افزودن',
                        'gate' => 'create',
                        'policy_class' => \Modules\User\Entities\User::class,
                        'icon' => 'fa fa-plus-circle',
                        'route' => 'admin.user.create',
                        'has_child' => false,
                        'children' => null,
                    ],
                ],
            ],
        ],
    ],
];
