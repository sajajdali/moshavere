<?php

return [
    'name' => 'Place',
    'permission' => [
        [
            'gate' => [
                'place' => 'مدیریت مطب ها',
            ],
            'type' => 'light',
            'display_name' => 'مدیریت مطب و کلینیک',
            'permissions' => [
                'place.create' => 'ایجاد مطب',
                'place.edit'   => 'ویرایش مطب',
                'place.delete' => 'حذف مطب',
            ],
        ],
    ],

    //menu can be find on the Speciality Module

];
