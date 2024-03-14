<?php
// menu is inside appointment Setting module
return [
    'name' => 'Absence',
    'permission' => [
        [
            'gate' => [
                'Absence' => 'تنظیمات عدم حضور',
            ],
            'type' => 'success',
            'display_name' => 'تنظیمات عدم حضور',
            'permissions' => [
                'Absence.create' => 'ایجاد عدم حضور',
                'Absence.delete' => 'حذف عدم حضور ',
            ],
        ],
    ],
];
