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
    'menu' => [
        'title' => 'ثبت نوبت ها',
        'gate' => ['appointment_user'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 80,
        'children' => [//it is required

            [
                'title' => 'ثبت نوبت جدید',
                'gate' => 'viewAny',
                'policy_class' => \Modules\User\Entities\User::class,
                'icon' => 'fe fe-plus-circle',
                'route' => 'admin.appointment_user.create',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],
        ],
    ],


];
