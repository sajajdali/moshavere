<?php

return [
    'name' => 'Discount',
    'permission' => [
        [
            'gate' => [
                'discount' => 'کد تخفیف',
            ],
            'type' => 'success',
            'display_name' => 'دسترسی به کد تخفیف',
            'permissions' => [
                'discount.update' => 'مدیریت کد تخفیف',
                'discount.delete' => 'حذف کد تخفیف',
            ],
        ],
    ],
    'menu' => [
        'title' => 'امور مالی',
        'gate' => 'viewAny',
        'policy_class' => \Modules\Discount\app\Models\Discount::class,
        'has_divider' => true,
        'priority' => 70,
        'children' => [ //it is required
            [
                'title' => 'کد تخفیف',
                'gate' => 'viewAny',
                'policy_class' => \Modules\Discount\app\Models\Discount::class,
                'icon' => 'fe fe-percent',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'افزودن',
                        'gate' => 'update',
                        'policy_class' => \Modules\Discount\app\Models\Discount::class,
                        'icon' => 'fe fe-percent',
                        'route' => 'admin.discount.create',
                        'has_child' => false,
                        'children' => null
                    ],
                    [
                        'title' => 'لیست',
                        'gate' => 'viewAny',
                        'policy_class' => \Modules\Discount\app\Models\Discount::class,
                        'icon' => 'fa fa-plus-circle',
                        'route' => 'admin.discount.list',
                        'has_child' => false,
                        'children' => null,
                    ],
                ],
            ],
        ],
    ],

];
