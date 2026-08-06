<?php

return [
    'name' => 'Chat',
    'permission' => [
        [
            'gate' => [
                'chat' => 'دسترسی به پیام های پشتیبانی',
            ],
            'type' => 'light',
            'display_name' => 'پیام های پشتیبانی',
            'permissions' => [
                'messageTemplate.create' => 'ایجاد پیام های پیشفرض',
                'messageTemplate.edit' => 'ویرایش پیام های پیشفرض',
                'messageTemplate.delete' => 'حذف پیام های پیشفرض'
            ],
        ],
    ],

    // 'menu' => [
    //     'title' => 'چت ها',
    //     'gate' => 'chat',
    //     'policy_class' => null,
    //     'has_divider' => true,
    //     'priority' => 80,
    //     'children' => [//it is required
    //         [
    //             'title' => 'پیام های پشتیبانی',
    //             'gate' => 'viewAny',
    //             'policy_class' => \Modules\Chat\app\Models\Chat::class,
    //             'icon' => 'fe fe-message-circle',
    //             'route' => 'admin.chat',
    //             'has_badge' => true,
    //             'badge' => [
    //                 'class' => \Modules\Chat\app\Models\Chat::class,
    //                 'type' => 'badge badge-sm bg-secondary badge-hide'
    //             ],
    //             'has_child' => false,
    //             'children' => null
    //         ],
    //         [
    //             'title' => 'قالب های آماده پیام',
    //             'gate' => 'viewAny',
    //             'policy_class' => \Modules\Chat\app\Models\Chat::class,
    //             'icon' => 'fe fe-edit',
    //             'route' => 'admin.messagetemplate.create',
    //             'has_badge' => false,
    //             'has_child' => false,
    //             'children' => null
    //         ],
    //     ],
    // ],
];
