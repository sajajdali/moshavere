<?php

return [
    'name' => 'AppointmentUser',
    'permission' => [
        [
            'gate' => [
                'appointment_user' => 'دسترسی به وعده ها',
            ],
            'type' => 'success',
            'display_name' => 'بخش ثبت نوبت',
            'permissions' => [
                'appointment_user.create' => 'ثبت نوبت',
                'appointment_user.edit'   => 'ویرایش نوبت',
                'appointment_user.delete' => 'حذف نوبت',
            ],
        ],
    ],
    // menu can be find in Appointment Setting module


];
