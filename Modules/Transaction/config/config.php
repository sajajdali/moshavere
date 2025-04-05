<?php

// menu can be find in discount 
return [
    'name' => 'Transaction',
    'permission' => [
        [
            'gate' => [
                'Transction' => 'دسترسی به تنظیمات حضور',
                'Transction.own' => 'دسترسی به تنظیمات حضور خود',
            ],
            'type' => 'light',
            'display_name' => 'تنظیمات زمان های حضور',
            'permissions' => [
                'Transction' => 'مدیریت تنظیمات',
                'Transction.delete' => 'مدیریت تنظیمات',

            ],
        ],
    ],
];
