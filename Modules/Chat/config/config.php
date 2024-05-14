<?php

return [
    'name' => 'Chat',
    'permission' => [
        [
            'gate' => [
                'chat' => 'پیام های کاربران',
            ],
            'type' => 'success',
            'display_name' => 'پشتیبانی',
            'permissions' => [
                'chat.update' => 'مدیریت تنظیمات',
            ],
        ],
    ],

    'menu' => [
        'title' => 'چت ها',
        'gate' => 'chat',
        'policy_class' => null,
        'has_divider' => true,
        'priority' => 80,
        'children' => [//it is required
            [
                'title' => 'پیام های پشتیبانی',
                'gate' => 'viewAny',
                'policy_class' => \Modules\Chat\app\Models\Chat::class,
                'icon' => 'fe fe-message-circle',
                'route' => 'admin.chat',
                'has_badge' => true,
                'badge' => [
                    'class' => \Modules\Chat\app\Models\Chat::class,
                    'type' => 'badge badge-sm bg-secondary badge-hide'
                ],
                'has_child' => false,
                'children' => null
            ],
        ],
    ],
];
