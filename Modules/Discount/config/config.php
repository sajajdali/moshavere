<?php

return [
    'name' => 'Discount',
    'permission' => [
        [
            'gate' => [
                'discount' => 'کد تخفیف',
            ],
            'type' => 'success',
            'display_name' => 'کد تخفیف',
            'permissions' => [
                'discount.update' => 'مدیریت کد تخفیف',
                'discount.delete' => 'حذف کد تخفیف',
            ],
        ],
    ],

    'menu' => [
        'title' => 'امور مالی',
        'gate' => ['discount'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 70,
        'children' => [//it is required
            [
                'title' => 'کد تخفیف',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-percent',
                'route' => 'admin.discount.create',
                'has_child' => false,
                'children' => null
            ],
        ],
    ],
];
