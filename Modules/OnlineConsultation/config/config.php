<?php

return [
    'name' => 'OnlineConsultation',
    'permission' => [[
        'gate' => ['ONLINE_CONSULTATION_MANAGE' => 'مدیریت مشاوره آنلاین'],
        'type' => 'light', 'display_name' => 'مشاوره آنلاین',
        'permissions' => [],
    ]],
    'menu' => [
        'title' => 'مشاوره آنلاین', 'gate' => ['ONLINE_CONSULTATION_MANAGE'],
        'policy_class' => null, 'has_divider' => true, 'children' => [
            ['title' => 'داشبورد مشاوره', 'gate' => ['ONLINE_CONSULTATION_MANAGE'], 'policy_class' => null, 'icon' => 'fe fe-phone', 'route' => 'admin.consultation.dashboard', 'has_badge' => false, 'has_child' => false, 'children' => null],
            ['title' => 'پزشکان و کارشناسان', 'gate' => ['ONLINE_CONSULTATION_MANAGE'], 'policy_class' => null, 'icon' => 'fe fe-phone', 'route' => 'admin.consultation.practitioners', 'has_badge' => false, 'has_child' => false, 'children' => null],
            ['title' => 'تنظیمات مشاوره', 'gate' => ['ONLINE_CONSULTATION_MANAGE'], 'policy_class' => null, 'icon' => 'fe fe-phone', 'route' => 'admin.consultation.settings', 'has_badge' => false, 'has_child' => false, 'children' => null],
            ['title' => 'لاگ تماس‌های VoIP', 'gate' => ['ONLINE_CONSULTATION_MANAGE'], 'policy_class' => null, 'icon' => 'fe fe-list', 'route' => 'admin.consultation.voip.logs', 'has_badge' => false, 'has_child' => false, 'children' => null],
        ]],
];
