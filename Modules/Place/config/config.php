<?php

return [
    'name' => 'Place',
    'permission' => [
        [
            'gate' => [
                'Place' => 'مدیریت مطب ها',
            ],
            'type' => 'success',
            'display_name' => 'مدیریت مطب و کلینیک',
            'permissions' => [
                'Place.update' => 'مدیریت تنظیمات',
            ],
        ],
    ],

    //menu can be find on the Speciality Module 

];
