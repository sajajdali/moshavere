<?php
// menu is inside appointment Setting module
return [
    'name' => 'Absence',
    'permission' => [
        [
            'gate' => [
                'absence' => 'تنظیمات عدم حضور',
            ],
            'type' => 'light',
            'display_name' => 'تنظیمات عدم حضور',
            'permissions' => [
                'absence.create' => 'ایجاد عدم حضور',
                'absence.delete' => 'حذف عدم حضور ',
            ],
        ],
    ],
];
