<?php

return [
    'name' => 'Service',
    'permission' => [
        [
            'gate' => [
                'Service' => 'تعاریف پایه',
            ],
            'type' => 'success',
            'display_name' => 'مدیریت بخش ها',
            'permissions' => [
                'Service.update' => 'ویرایش بخش',
                'Service.create' => 'اضافه کردن بخش',
                'Service.delete' => 'حذف بخش',
            ],
        ],
    ],

    //menu can be find on "speciality" module conf file
];
