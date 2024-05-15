<?php

return [
    'name' => 'AppointmentUser',
    'permission' => [
        [
            'gate' => [
                'appointment_user' => 'دسترسی به نوبت ها',
            ],
            'type' => 'success',
            'display_name' => 'بخش ثبت نوبت',
            'permissions' => [
                'appointment_user.addApp' => 'ثبت نوبت',
                'appointment_user.edit'   => 'ویرایش نوبت',
                'appointment_user.delete' => 'حذف نوبت',
                'appointment_user.list'   => 'مشاهده تمامی نوبت ها',
                'appointment_user.online' => 'فقط نوبت های آنلاین',
                'appointment_user.message'  => 'پیام های نوبت آنلاین',
            ],
        ],
    ],
    // menu can be find in Appointment Setting module


];
