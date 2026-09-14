<?php

return [
    'name' => 'PractitionerApi',
    'version' => '1.0.0',
    'path' => 'api/practitioner/v1',
    'token_ability' => 'practitioner-app',
    'otp' => [
        'digits' => 4,
        'ttl_seconds' => 120,
        'resend_after_seconds' => 60,
        'max_attempts' => 5,
        'lock_minutes' => 15,
    ],
    'token_expiration_days' => 90,
    'landing' => [
        'app_subtitle' => 'پنل پزشکان و مشاوران',
        'headline' => 'نوبت‌ها، تماس‌ها و درآمد شما در یک جا',
        'description' => 'نوبت‌های آنلاین، تماس‌ها، گزارش مشاوره و تسویه مالی خود را در اپلیکیشن مدیریت کنید.',
        'features' => [
            ['order' => 1, 'title' => 'نوبت‌های امروز با شمارش معکوس', 'body' => 'زمان شروع و پایان هر نوبت را دقیق مشاهده کنید.'],
            ['order' => 2, 'title' => 'تماس روی داخلی VoIP', 'body' => 'تماس بیمار از طریق سامانه به داخلی شما متصل می‌شود.'],
            ['order' => 3, 'title' => 'گزارش و تسویه شفاف', 'body' => 'گزارش، مدت مکالمه و وضعیت مالی هر نوبت را یک‌جا ببینید.'],
        ],
        'cta_label' => 'ورود به پنل پزشک',
        'footnote' => 'ورود فقط برای پزشکان و مشاوران فعال مجموعه',
        'terms_url' => null,
        'min_supported_version' => '1.0.0',
    ],
    'settings' => [
        'push_appointments' => ['label' => 'اعلان نوبت‌های جدید', 'default' => true],
        'push_calls' => ['label' => 'اعلان تماس ورودی', 'default' => true],
        'ring_sound' => ['label' => 'صدای زنگ', 'default' => true],
        'auto_note_save' => ['label' => 'ذخیره خودکار یادداشت', 'default' => true],
    ],
];
