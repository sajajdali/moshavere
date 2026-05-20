<?php

// menu can be find in discount
return [
    'name' => 'Transaction',
    'permission' => [
        [
            'gate' => [
                'Transction' => 'دسترسی به تراکنش ها',
            ],
            'type' => 'light',
            'display_name' => 'دسترسی به تراکنش ها',
            'permissions' => [
                'Transction' => 'مشاهده تراکنش ها',
                'Transction.delete' => 'حذف تراکنش ها',

            ],
        ],
    ],
];
