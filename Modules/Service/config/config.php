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
                'Service.update' => 'مدیریت بخش ها',
            ],
        ],
    ],

    //menu can be find on "speciality" module conf file
];
