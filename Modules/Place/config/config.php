<?php

return [
    'name' => 'Place',
    'permission' => [
        [
            'gate' => [
                'Place' => 'مدیریت مطب ها',
            ],
            'type' => 'success',
            'display_name' => 'مدیریت مطب و کلینیک',
            'permissions' => [
                'Place.update' => 'مدیریت تنظیمات',
            ],
        ],
    ],

    'menu' => [
        'title' => 'مکان ها',
        'gate' => ['Place'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 70,
        'children' => [//it is required
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
        ],
    ],
];
