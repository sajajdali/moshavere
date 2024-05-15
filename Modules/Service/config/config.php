<?php

return [
    'name' => 'Service',
    'permission' => [
        [
            'gate' => [
                'service' => 'بخش ها',
            ],
            'type' => 'success',
            'display_name' => 'دسترسی به بخش ها',
            'permissions' => [
                'service.update' => 'ویرایش بخش',
                'service.create' => 'اضافه کردن بخش',
                'service.delete' => 'حذف بخش',
            ],
        ],
    ],

    //menu can be find on "speciality" module conf file
];
