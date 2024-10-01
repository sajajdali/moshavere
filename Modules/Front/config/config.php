<?php

//  comment.own permition is define in appointment_user 
$menu = [
    'name' => 'Front',
// permistion define in appointmentUser
    'menu' => [
        'title' => 'نمایش',
        'gate' => ['appointment_user.comment', 'appointment_user.feedback','comment.own'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 70,
        'children' => [
            [
                'title' => 'نظر سنجی ',
                'gate' => 'appointment_user.feedback',
                'policy_class' => null,
                'icon' => 'fe fe-activity',
                'route' => 'admin.appointment.feedback',
                'has_badge' => false,
                'has_child' => false,
                'children' => null
            ],

        ],
    ],

];

if (!disableUi()) {
    array_push($menu['menu']['children'],
        [
            'title' => 'سوالات متداول',
            'gate' => 'faq',
            'policy_class' => Modules\Front\app\Models\Faq::class,
            'icon' => 'fe fe-help-circle',
            'route' => 'admin.faq',
            'has_badge' => false,
            'has_child' => false,
            'children' => null
        ],
        [
            'title' => 'کامنت ها',
            'gate' => ['appointment_user.comment','comment.own'],
            'policy_class' => null,
            'icon' => 'fe fe-book',
            'route' => 'admin.comment',
            'has_badge' => false,
            'has_child' => false,
            'children' => null
        ]);
}
return $menu;
