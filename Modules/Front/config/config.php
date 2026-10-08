<?php

//  comment.own permition is define in appointment_user
$menu = [
    'name' => 'Front',
// permistion define in appointmentUser
    'menu' => [
        'title' => 'نمایش',
        'gate' => ['appointment_user.comment', 'appointment_user.feedBack','comment.own','contact-us'],
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 70,
        'children' => [
            [
                'title' => 'نظرسنجی',
                'gate' => 'appointment_user.feedBack',
                'policy_class' => null,
                'icon' => 'fe fe-activity',
                'route' => null,
                'has_badge' => false,
                'has_child' => true,
                'children' => [
                    [
                        'title' => 'فرم های نظرسنجی',
                        'gate' => 'appointment_user.feedBack',
                        'policy_class' => null,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.feedback.forms',
                        'has_child' => false,
                        'children' => null,
                    ],
                    [
                        'title' => 'پاسخ های فرم های نظرسنجی',
                        'gate' => 'appointment_user.feedBack',
                        'policy_class' => null,
                        'icon' => 'fa fa-list',
                        'route' => 'admin.appointment.feedback.answers',
                        'has_child' => false,
                        'children' => null,
                    ],
                ],
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
        ],
        [
            'title' => 'درخواست های مشاوره',
            'gate' => ['contact-us'],
            'policy_class' => null,
            'icon' => 'fe fe-printer',
            'route' => 'admin.contactus',
            'has_badge' => false,
            'has_child' => false,
            'children' => null
        ]);
}
return $menu;
