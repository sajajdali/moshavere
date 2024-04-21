<?php

return [
    'name' => 'Reminder',
    'permission' => [
        [
            'gate' => [
                'reminder' => 'تنظیمات یادآوری',
            ],
            'type' => 'success',
            'display_name' => 'تنظیمات یادآوری',
            'permissions' => [
                'reminder.update' => 'مدیریت یادآوری',
                'reminder.delete' => 'حذف یادآوری',
            ],
        ],
    ],
    //menu can be find on SPECIALITY module 
];
