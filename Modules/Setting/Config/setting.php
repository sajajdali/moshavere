<?php

$setting = [
    'sms' => [
        'title' => 'پیامک',
        'icon' => 'fa fa-mobile',
        'disable_ui' => disableUi(),
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_API_LOGIN_TEMPLATE,
            \Modules\Setting\Enum\SettingKeyEnum::CALL_LOGIN_TEMPLATE,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_SET_APP_MONITORING,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_AFTER_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_CANCEL,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TO_DOCTOR,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_TO_OPERATOR,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_FEEDBACK,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_AFTER_REFUND,
            \Modules\Setting\Enum\SettingKeyEnum::SMS_FOR_SEND_MESSAGE_IN_CHATS,

        ],
    ],
    'website' => [
        'title' => 'تنظمات وبسایت',
        'icon' => 'fa fa-globe',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SITE_LOGO_URL,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_SLIDER_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::INSTAGRAM_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::WHATSAPP_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::TELEGRAM_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_FIRST_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_SECEND_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::SITE_SECEND_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_GALLERY_BODY,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_ENAMAD,
            \Modules\Setting\Enum\SettingKeyEnum::FOOTER_SAMANDEHI,
            \Modules\Setting\Enum\SettingKeyEnum::UI_NOW_SHOW_SEARCH_BAR,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_CITY_SEARCH,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_LATEST_DOCTORS,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_HOME_FAQ,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_MOST_VIEWED_SECTIONS,
            \Modules\Setting\Enum\SettingKeyEnum::MOST_VIEWED_SECTIONS_ICONS_VIEW,
            \Modules\Setting\Enum\SettingKeyEnum::DISABLE_FOOTER_DISPLAY,
            \Modules\Setting\Enum\SettingKeyEnum::SHOW_FLOATING_SOCIAL_ICONS,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTOR_REGISTRATION,

        ],
    ],
    'header' => [
        'title' => 'تنظیمات هدر',
        'icon' => 'fa fa-header',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTORS_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::ENABLE_CONTACT_US_MENU,
            \Modules\Setting\Enum\SettingKeyEnum::ACTIVE_HEADER,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_IMAGE,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE1,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE2,
//            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTONS,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON1,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_TITLE1,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_HREF1,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_SHOW_BUTTON2,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_TITLE2,
            \Modules\Setting\Enum\SettingKeyEnum::HEADER1_BUTTON_HREF2,

        ]
    ],
    'appointment' => [
        'title' => 'نوبت دهی',
        'icon' => 'fa fa-fire',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_FOR_OTHERS_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_MORE_THAT_ONE_PER_DAY,
            \Modules\Setting\Enum\SettingKeyEnum::SHOW_FALSE_APPOINTMENT_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_SHOW_FALSE_STATUS_DAYS,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DESCRIPTION_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_CANCEL_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION,
            \Modules\Setting\Enum\SettingKeyEnum::SECREYERY_SEND_LINK_FOR_APPOINTMENT,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_ADMIN,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DEADLINE_VIA_USER,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT,
            \Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_ONLINE_DESCRPTION,


        ],
    ],
    'support' => [
        'title' => 'پشتیبانان',
        'icon' => 'fa fa-user',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::SUPPORT_USER_ROLE
        ],
    ],
    'api' => [
        'title' => 'تنظیمات API',
        'icon' => 'fa fa-api',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::ACTIVE_API,
            \Modules\Setting\Enum\SettingKeyEnum::API_USERNAME,
            \Modules\Setting\Enum\SettingKeyEnum::API_PASSWORD
        ],
    ],
    'payment' => [
        'title' => 'تنظیمات پرداخت',
        'icon' => 'fa fa-credit-card',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PAYSTAR_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PAYSTAR_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PAYSTAR_SIGN,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_ZARINPAL_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PARSIAN_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_PARSIAN_TOKEN,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMENT_RULES_AND_CONDITION_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::PAYMEN_ACTIVE_DRIVER,
        ],
    ],
    'voip' => [
        'title' => 'تنظیمات Voip',
        'icon' => 'fa fa-phone',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::VOIP_USERNAME,
            \Modules\Setting\Enum\SettingKeyEnum::VOIP_PASSWORD,
        ],
    ],
    'APP' => [
        'title' => 'تنظیمات اپ',
        'icon' => 'fa fa-gear',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::WEIGHT_CHART_DESCRIPTION_APP,
        ],
    ],
    'Contact_us' => [
        'title' => 'صفحه ی تماس با ما',
        'disable_ui' => disableUi(),
        'icon' => 'fa fa-retweet',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FIRST_SECTION_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FIRST_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FIRST_SECTION_BODY,
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FORM_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FORM_ADDRESS,
            \Modules\Setting\Enum\SettingKeyEnum::CONTACTUS_FORM_SUPPORT_EMAIL,
        ],
    ],
    'About_us' => [
        'title' => 'صفحه ی درباره ی ما',
        'icon' => 'fa fa-address-card-o',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FIRST_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FIRST_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_SECEND_SECTION_IMAGE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_THIRD_SECTION_IMAGE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_DESCRIPTION,
            \Modules\Setting\Enum\SettingKeyEnum::ABOUT_US_FOURTH_SECTION_IMAGE,
        ],
    ],
    'Online_appointment' => [
        'title' => 'نوبت آنلاین',
        'icon' => 'fa fa-desktop',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS,
            \Modules\Setting\Enum\SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE,
        ],
    ],
    'APPLICATION' => [
        'title' => 'اپلیکیشن',
        'icon' => 'fa fa-paperclip',
        'settings' => [
            \Modules\Setting\Enum\SettingKeyEnum::APP_FULL_APPOINTMENT_HEADER,
            \Modules\Setting\Enum\SettingKeyEnum::APP_FULL_APPOINTMENT_BODY,
        ],
    ],
];

return $setting;
