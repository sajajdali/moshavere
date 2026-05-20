<?php

namespace Modules\Setting\Enum;

use Modules\User\Entities\User;
use App\interface\EnumHasNameInterface;
use Modules\Setting\Interface\SettingTypeInterface;
use Modules\Setting\Interface\SettingHasCacheInterface;
use Modules\Setting\Interface\SettingHasOptionInterface;
use Modules\Setting\Interface\SettingRenderAbleInterface;

enum SettingKeyEnum: int implements EnumHasNameInterface, SettingTypeInterface, SettingHasCacheInterface, SettingRenderAbleInterface, SettingHasOptionInterface
{
    case DEFAULT_EXERCISE_STATUS = 1;
    case SITE_LOGO_URL = 2;
    case SITE_TITLE = 3;
    case APPOINTMENT_STATUS = 4;
    case APPOINTMENT_DESCRIPTION_STATUS = 5;
    case APPOINTMENT_STORE_FROM_ID_STATUS = 75;
    case APPOINTMENT_DESCRIPTION = 6;
    case APPOINTMENT_CANCEL_DESCRIPTION = 7;
    case APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION = 8;
    case APPOINTMENT_DEADLINE_VIA_ADMIN = 9;
    case APPOINTMENT_DEADLINE_VIA_USER = 10;
    case SITE_SLIDER_TITLE = 11;
    case APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND = 12;
    case APPOINTMENT_FOR_OTHERS_STATUS = 13;
    case APPOINTMENT_MORE_THAT_ONE_PER_DAY = 14;
    case APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER = 220;
    case APPOINTMENT_SHOW_DESCRIPTION_IN_APP_LIST = 221;
    case FOOTER_DESCRIPTION = 15;
    case INSTAGRAM_ADDRESS = 16;
    case TELEGRAM_ADDRESS = 17;
    case SHOW_FALSE_APPOINTMENT_STATUS = 18;
    case SITE_FIRST_SECTION_TITLE = 19;
    case SITE_FIRST_SECTION_DESCRIPTION = 20;
    case SITE_SECEND_SECTION_TITLE = 21;
    case SITE_SECEND_SECTION_DESCRIPTION = 22;
    case APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS = 400;
    case APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT = 401;
    case APPOINTMENT_SHOW_FALSE_STATUS_DAYS = 402;
    case APPOINTMENT_GALLERY_TITLE = 403;
    case APPOINTMENT_GALLERY_BODY = 404;
    case APPOINTMENT_ONLINE_DESCRPTION = 405;
    case UI_NOW_SHOW_SEARCH_BAR = 406;
    case ENABLE_CITY_SEARCH = 407;
    case ENABLE_LATEST_DOCTORS = 408;
    case ENABLE_HOME_FAQ = 409;
    case ENABLE_MOST_VIEWED_SECTIONS = 410;
    case DISABLE_FOOTER_DISPLAY = 411;
    case SHOW_FLOATING_SOCIAL_ICONS = 412;
    case WHATSAPP_ADDRESS = 413;
    case ENABLE_DOCTOR_REGISTRATION = 414;
    case FOOTER_ENAMAD = 415;
    case FOOTER_SAMANDEHI = 416;
    case ACTIVE_HEADER = 417;
    case HEADER1_IMAGE = 418;
    case HEADER1_TITLE1 = 419;
    case HEADER1_TITLE2 = 427;
    case HEADER1_SHOW_BUTTONS = 420;
    case HEADER1_SHOW_BUTTON1 = 421;
    case HEADER1_SHOW_BUTTON2 = 422;
    case HEADER1_BUTTON_TITLE1 = 423;
    case HEADER1_BUTTON_TITLE2 = 424;
    case HEADER1_BUTTON_HREF1 = 425;
    case HEADER1_BUTTON_HREF2 = 426;
    case MOST_VIEWED_SECTIONS_ICONS_VIEW = 428;
    case ENABLE_DOCTORS_MENU = 430;
    case ENABLE_CONTACT_US_MENU = 431;
    case ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL = 432;

    // ABOUT US PAGE
    case ABOUT_US_FIRST_SECTION_TITLE = 23;
    case ABOUT_US_FIRST_SECTION_DESCRIPTION = 24;
    case ABOUT_US_SECEND_SECTION_TITLE = 25;
    case ABOUT_US_SECEND_SECTION_DESCRIPTION = 26;
    case ABOUT_US_SECEND_SECTION_IMAGE = 27;
    case ABOUT_US_THIRD_SECTION_TITLE = 28;
    case ABOUT_US_THIRD_SECTION_DESCRIPTION = 29;
    case ABOUT_US_THIRD_SECTION_IMAGE = 30;
    case ABOUT_US_FOURTH_SECTION_TITLE = 31;
    case ABOUT_US_FOURTH_SECTION_DESCRIPTION = 32;
    case ABOUT_US_FOURTH_SECTION_IMAGE = 33;

        //sms
    case SMS_SENDER = 79;
    case SMS_API_TOKEN = 60;
    case SMS_API_LOGIN_TEMPLATE = 61;
    case SMS_APPOINTMENT_RECEIVING_SUCCESSFUL = 62;
    case SMS_APPOINTMENT_WAITING_PAYMENT = 63;
    case SMS_APPOINTMENT_AFTER_PAYMENT = 64;
    case SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT = 65;
    case SMS_APPOINTMENT_TIME_UPDATE = 66;
    case SMS_APPOINTMENT_CANCEL = 67;
    case SMS_APPOINTMENT_TO_DOCTOR = 68;
    case SMS_APPOINTMENT_TO_OPERATOR = 69;
    case SMS_APPROVED_MONITORING_APPOINTMENT = 70;
    case SMS_DIS_APPROVED_MONITORING_APPOINTMENT = 71;
    case SMS_FEEDBACK = 72;
    case SUPPORT_USER_ROLE = 73;
    case SMS_AFTER_REFUND = 74;
    case SMS_FOR_SEND_MESSAGE_IN_CHATS = 76;
    case SMS_SET_APP_MONITORING = 77;
    case CALL_LOGIN_TEMPLATE = 78;
    case SMS_PARSSMS_SENDER_NUMBER =  85;
    case SMS_PARSSMS_LOGIN_TEXT = 80 ;
    case SMS_PARSSMS_ADD_APPOINTMENT_TEXT = 81 ;
    case SMS_PARSSMS_EDIT_APPOINTMENT_TEXT = 82 ;
    case SMS_PARSSMS_CANCEL_APPOINTMENT_TEXT = 83 ;
    case SMS_PARSSMS_REMINDER_TEXT = 84 ;
    case SMS_PRRSSMS_MONITORING_APP = 86 ;
    case SMS_PARSSMS_MONITORING_APPROVED_APP = 87 ;
    case SMS_PARSSMS_MONITORING_DIS_APPROVED_APP = 89 ;
    case SMS_PRSSMS_APPOINTMENT_WAITING_PAYMENT = 91 ;
    case SMS_FARAZ_LINE_NUMBER = 92 ;
    case DONT_SEND_SMS_FOR_PAYMENT_LINK = 168 ;


        //payment
    case PAYMENT_PAYSTAR_TOKEN = 150;
    case PAYMENT_PAYSTAR_SIGN = 151;
    case PAYMENT_ZARINPAL_MERCHENID = 154;
    case PAYMENT_RULES_AND_CONDITION_STATUS = 155;
    case PAYMENT_RULES_AND_CONDITION_DESCRIPTION = 156;
    case SECREYERY_SEND_LINK_FOR_APPOINTMENT = 157;
    case PAYMEN_ACTIVE_DRIVER = 158;
    case PAYMENT_PARSIAN_TOKEN = 161;
    case PAYMENT_SAMAN_TERMINAL_NUMBER = 162;
    case PAYMENT_SAMAN_TERMINAL_PASS = 163;
    case PAYMENT_SEP_TERMINAL_ID = 164;
    case GO_TO_PAYMENT_DIRECTLY = 167;



    case WEIGHT_CHART_DESCRIPTION_APP = 120;
    case VOIP_USERNAME = 160;
    case VOIP_PASSWORD = 170;

    // api
    case ACTIVE_API = 180;
    case API_USERNAME = 181;
    case API_PASSWORD = 182;
        // contact us page
    case CONTACTUS_FIRST_SECTION_STATUS = 210;
    case CONTACTUS_FIRST_SECTION_TITLE = 211;
    case CONTACTUS_FIRST_SECTION_BODY = 212;
    case CONTACTUS_FORM_STATUS = 213;
    case CONTACTUS_FORM_ADDRESS = 214;
    case CONTACTUS_FORM_SUPPORT_EMAIL = 215;

    // onlineApp
    case ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS = 350 ;
    case ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE = 351 ;

    // application
    case APP_FULL_APPOINTMENT_HEADER = 352 ;
    case APP_FULL_APPOINTMENT_BODY = 353 ;

    // front menu
    case SHOW_ABOUT_US_MENU_BUTTON = 354 ;

    // registration
    case USER_REGISTER_NATIONAL_CODE_REQUIRED = 355 ;
    case LOGIN_WITHOUT_OTP = 357;

    // jibi
    case ACTIVE_JIBIT = 356 ;

    public function isSupportCache(): bool
    {
        return match ($this) {
            default => false
        };
    }

    public function getName(): string
    {
        return match ($this) {
            //Website setting
            self::SITE_LOGO_URL => 'آدرس لوگو',
            self::SITE_TITLE => 'عنوان سایت',
            self::APPOINTMENT_STATUS => 'فعال بودن نوبت دهی',
            self::APPOINTMENT_FOR_OTHERS_STATUS => 'امکان ثبت نوبت برای دیگران',
            self::APPOINTMENT_DESCRIPTION_STATUS => 'فعال بودن توضیحات در صفحه جزئیات نوبت',
            self::APPOINTMENT_DESCRIPTION => 'توضیحات مربوط به صفحه جزئیات نوبت',
            self::APPOINTMENT_CANCEL_DESCRIPTION => 'توضیحات مربوط به کنسلی نوبت',
            self::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION => 'فعال بودن ثبت حضور و یا عدم حضور بیمار',
            self::APPOINTMENT_DEADLINE_VIA_ADMIN => 'مدت زمان رزرو بودن نوبت برای پرداخت در زمانی که وضعیت نوبت در انتظار پرداخت میباشد و نوبت از طریق پنل ادمین ثبت شده باشد (ساعت)',
            self::APPOINTMENT_DEADLINE_VIA_USER => 'مدت زمان رزرو بودن نوبت برای پرداخت در زمانی که وضعیت نوبت در انتظار پرداخت میباشد و نوبت را بیمار دریافت کرده باشد(ساعت)',
            self::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER => 'ثبت نوبت با شماره پرونده در پنل منشی',
            self::SITE_SLIDER_TITLE => 'عنوان در ابتدای صفحه ای اصلی و بالای قسمت جست و جو',
            self::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND => 'توضیحات در صفحه قبل از تایید نوبت توس کاربر(checkout)',
            self::APPOINTMENT_MORE_THAT_ONE_PER_DAY => 'امکان رزرو بیشتر از یک نوبت در هر روز برای هر بیمار',
            self::FOOTER_DESCRIPTION => 'توضیحات در فورتر سایت',
            self::INSTAGRAM_ADDRESS => 'ادرس صفحه ی ابنتساگرام شما: ',
            self::WHATSAPP_ADDRESS => 'ادرس صفحه ی واتس آپ شما: ',
            self::ENABLE_DOCTOR_REGISTRATION => 'فعال بودن ثبت نام پزشک: ',
            self::TELEGRAM_ADDRESS => 'ادرس تلگرام شما ',
            self::SHOW_FALSE_APPOINTMENT_STATUS => 'نمایش ساعت های پر شده در لیست ساعت ها به کاربران',
            self::SITE_FIRST_SECTION_TITLE => 'عنوان بخش اول در صفحه ی اصلی(عنوان پیشنهادی: ویزیت فوری)',
            self::SITE_FIRST_SECTION_DESCRIPTION => 'توضیح بخش اول در صفحه ی اصلی',
            self::SITE_SECEND_SECTION_TITLE => 'عنوان بخش دوم در صفحه ی اصلی(عنوان پیشنهادی: معرفی پزشکان)',
            self::SITE_SECEND_SECTION_DESCRIPTION => 'توضیح بخش دوم در صفحه ی اصلی)',
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS => ' وضعیت توضیحات در صفحه ی جزئیات نوبت که مربوط به پرداخت میباشد',
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT => 'متن توضیحات در صفحه ی جزئیات نوبت که مربوط به پرداخت میباشد ',
            self::APPOINTMENT_SHOW_FALSE_STATUS_DAYS => 'در قسمت دریافت نوبت ، روز هایی که تمامی نوبت آنها پر هست به کاربر نمایش دهد',
            self::APPOINTMENT_GALLERY_TITLE => 'تیتر نمایش گالری پزشک',
            self::FOOTER_ENAMAD => 'ای نماد (لینک کامل درج شود)',
            self::ACTIVE_HEADER => 'هدر فعال',
            self::HEADER1_IMAGE => 'عکس اصلی هدر',
            self::HEADER1_TITLE1 => 'تیتر اصلی هدر',
            self::HEADER1_TITLE2 => 'تیتر دوم هدر',
            self::HEADER1_SHOW_BUTTONS => 'نمایش کلید ها',
            self::HEADER1_SHOW_BUTTON1 => 'نمایش کلید اول',
            self::HEADER1_BUTTON_TITLE1 => 'تیتر کلید اول',
            self::HEADER1_BUTTON_HREF1 => 'لینک کلید اول',
            self::HEADER1_SHOW_BUTTON2 => 'نمایش کلید دوم',
            self::HEADER1_BUTTON_TITLE2 => 'تیتر کلید دوم',
            self::HEADER1_BUTTON_HREF2 => 'لینک کلید دوم',
            self::FOOTER_SAMANDEHI => 'نماد سامان دهی (لینک کامل درج شود)',
            self::APPOINTMENT_GALLERY_BODY => 'متن نمایش گالری پزشک',
            self::APPOINTMENT_ONLINE_DESCRPTION => 'برای نوبت های آنلاین ، توضیحات قبل از دریافت نوبت',
            self::UI_NOW_SHOW_SEARCH_BAR => 'غیر بودن جست و جو در صفحه اصلی',
            self::ENABLE_CITY_SEARCH => 'فعال  بودن جست و جو بر اساس شهر',
            self::ENABLE_LATEST_DOCTORS => 'فعال بودن جدید ترین پزشکان صفحه اصلی',
            self::ENABLE_HOME_FAQ => 'فعال بودن سوالات متداول صفحه اصلی',
            self::ENABLE_MOST_VIEWED_SECTIONS => 'فعال بودن پربازدید ترین بخش ها',
            self::MOST_VIEWED_SECTIONS_ICONS_VIEW => 'نمایش بخش های صفحه اصلی به صورت تک صفحه و بدون اسلاید',
            self::DISABLE_FOOTER_DISPLAY => 'غیر فعال شدن فوتر',
            self::SHOW_FLOATING_SOCIAL_ICONS => 'نمایش ایکون های اینستاگرام و واتس اپ به صورت شناور',
            self::ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL => 'اجازه ثبت نوبت در پنل منشی، برای ساعت هایی که از قبل یک نوبت ثبت شده در آن ساعت وجود دارد',
            self::APPOINTMENT_SHOW_DESCRIPTION_IN_APP_LIST => 'نمایش توضیحات مربوط به نوبت در صفحه ی لیست نوبت ها',
            self::SHOW_ABOUT_US_MENU_BUTTON => 'نمایش درباره ما در منو',

            // SMS
            self::SMS_API_TOKEN => 'توکن API پیامک',
            self::DEFAULT_EXERCISE_STATUS => 'وضعیت برنامه بعد از تجویز',
            self::SUPPORT_USER_ROLE => 'گروه کاربری پشتیبانان',
            self::SMS_API_LOGIN_TEMPLATE => 'الگو پیامک ورود',
            self::WEIGHT_CHART_DESCRIPTION_APP => 'متن توضیح در صفحه ی مشاهده مودار وزنی ',
            self::SMS_AFTER_REFUND => 'نام الگوی پیامکی، بعد از استرداد وجه',
            self::SMS_FOR_SEND_MESSAGE_IN_CHATS => 'نام الگوی پیامکی، بعد از پاسخ دادن به چت',
            self::SMS_SET_APP_MONITORING => 'در صورت فعال بودن پایش نوبت، پیامک ثبت نوبت',
            self::SMS_SENDER => 'پنل ارسال کننده ی پیامک',
            self::SMS_FARAZ_LINE_NUMBER => 'شماره ارسال کننده پیامک',

            //payment
            self::PAYMENT_PAYSTAR_TOKEN => 'کد درگاه پرداخت پی استار',
            self::PAYMENT_PAYSTAR_SIGN => 'امضا درگاه پی استار',
            self::PAYMENT_ZARINPAL_MERCHENID => 'مرچند ایدی درگاه زرین پال',
            self::PAYMENT_RULES_AND_CONDITION_STATUS => 'فعال سازی شرایط و قوانین پرداخت',
            self::PAYMENT_RULES_AND_CONDITION_DESCRIPTION => 'شرایط و قوانین مربوط به پرداخت',
            self::SECREYERY_SEND_LINK_FOR_APPOINTMENT => 'امکان ارسال لینک پرداخت نوبت به کاربر توسط منشی',
            self::PAYMEN_ACTIVE_DRIVER => 'درگاه فعال',
            self::PAYMENT_PARSIAN_TOKEN => 'کد PIN Code دریافتی از بانک پارسیان',
            self::PAYMENT_SAMAN_TERMINAL_NUMBER => 'شماره ترمینال سامان(MID)',
            self::PAYMENT_SAMAN_TERMINAL_PASS => 'رمز ترمینال سامان',
            self::PAYMENT_SEP_TERMINAL_ID => 'شماره ترمینال سپ',
            self::GO_TO_PAYMENT_DIRECTLY => 'انتقال مستقیم به درگاه بعد از انتخاب گزینه تایید نوبت',

            // headers
            self::ENABLE_DOCTORS_MENU => 'فعال بودن لیست پزشکان در منو',
            self::ENABLE_CONTACT_US_MENU => 'فعال بودن  ارتباط با ما در منو',
            // sms
            self::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL => 'پیامک به کاربر پس از دریافت نوبت موفق',
            self::SMS_APPOINTMENT_WAITING_PAYMENT => 'پیامک به کاربر در صورتی که پرداخت فعال باشد و نوبت برای کاربر ثبت شود (نوبتی که نیاز به پرداخت دارد)',
            self::SMS_APPOINTMENT_AFTER_PAYMENT => 'پیامک به کاربر پس از پرداخت موفق هزینه',
            self::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT => 'پیامک به کاربر در صورتی که پرداخت نکند و نوبت وی حذف شود',
            self::SMS_APPOINTMENT_TIME_UPDATE => 'پیامک به کاربر پس از ویرایش زمان نوبت',
            self::SMS_APPOINTMENT_CANCEL => 'پیامک به کاربر پس از کنسل شدن نوبت',
            self::SMS_APPOINTMENT_TO_DOCTOR => 'پیامک به پزشک بعد از دریافت نوبت توسط هر کاربر',
            self::SMS_APPOINTMENT_TO_OPERATOR => 'پیامک به اپراتور پس از دریافت هر نوبت',
            self::SMS_APPROVED_MONITORING_APPOINTMENT => 'پیامک به کاربر بعد از تایید نوبت در  پایش نوبت',
            self::SMS_DIS_APPROVED_MONITORING_APPOINTMENT => 'پیامک به کاربر بعد از عدم تایید نوبت در  پایش نوبت',
            self::SMS_FEEDBACK => 'پیامک ارسال نظر سنجی به کاربر، بعد از ثبت حضور کاربر',
            self::CALL_LOGIN_TEMPLATE => 'الگوی تماس برای ورود کاربر',
            self::DONT_SEND_SMS_FOR_PAYMENT_LINK => 'غیر فعال سازی ارسال پیامک در زمانی که پرداخت فعال است و بیماران نوبت در انتظار پرداخت دریافت میکنند',

            // PARS SMS
            self::SMS_PARSSMS_LOGIN_TEXT => 'متن پیامک ورود',
            self::SMS_PARSSMS_ADD_APPOINTMENT_TEXT => 'متن پیامک بعد از دریافت نوبت موفق',
            self::SMS_PARSSMS_EDIT_APPOINTMENT_TEXT => 'متن پیامک ارسالی بعد از ویرایش نوبت',
            self::SMS_PARSSMS_CANCEL_APPOINTMENT_TEXT => 'متن پیامک ارسالی بعد از کنسل کردن نوبت',
            self::SMS_PARSSMS_REMINDER_TEXT => 'متن پیامک ارسالی برای یادآوری نوبت',
            self::SMS_PARSSMS_SENDER_NUMBER => 'شماره ارسال پیامک در پنل',
            self::SMS_PRRSSMS_MONITORING_APP => 'متن نوبت های در انتظار پایش',
            self::SMS_PARSSMS_MONITORING_APPROVED_APP => 'متن پیامک بعد از تایید نوبت های پایش نوبت',
            self::SMS_PARSSMS_MONITORING_DIS_APPROVED_APP => 'متن پیامک بعد از رد نوبت های پایش نوبت',
            self::SMS_PRSSMS_APPOINTMENT_WAITING_PAYMENT => 'متن پیامک در انتظار پرداخت',

            // voip
            self::VOIP_USERNAME => 'نام کاربری برای API ',
            self::VOIP_PASSWORD => 'کلمه عبور برای API ',


            // api
            self::ACTIVE_API => 'فعال سازی API',
            self::API_USERNAME => 'نام کاربری api',
            self::API_PASSWORD => ' کلمه عبور api',

            //contact us
            self::CONTACTUS_FIRST_SECTION_STATUS => 'فعال سازی بخش اول در صفحه تماس با ما  ',
            self::CONTACTUS_FIRST_SECTION_TITLE => 'عنوان در بخش اول در صفحه ی تماس با ما   ',
            self::CONTACTUS_FIRST_SECTION_BODY => 'متن اصلی در بخش اول در صفحه ی تماس با ما   ',
            self::CONTACTUS_FORM_STATUS => 'فعال بودن فرم ارسال پیام ',
            self::CONTACTUS_FORM_ADDRESS => 'ادرس نمایشی در صفحه ی تماس با ما ',
            self::CONTACTUS_FORM_SUPPORT_EMAIL => 'ایمیل نمایشی برای ارتباط با پشتیبانی ',

            // about us
            self::ABOUT_US_FIRST_SECTION_TITLE => 'عنوان بخش اول',
            self::ABOUT_US_FIRST_SECTION_DESCRIPTION => 'توضیحات بخش اول',
            self::ABOUT_US_SECEND_SECTION_TITLE => 'عنوان بخش دوم',
            self::ABOUT_US_SECEND_SECTION_DESCRIPTION => 'توضیحات بخش دوم',
            self::ABOUT_US_SECEND_SECTION_IMAGE => 'تصویر بخش دوم',
            self::ABOUT_US_THIRD_SECTION_TITLE => 'عنوان بخش سوم',
            self::ABOUT_US_THIRD_SECTION_DESCRIPTION => 'توضیحات بخش سوم',
            self::ABOUT_US_THIRD_SECTION_IMAGE => 'تصویر بخش سوم',
            self::ABOUT_US_FOURTH_SECTION_TITLE => 'عنوان بخش چهارم',
            self::ABOUT_US_FOURTH_SECTION_DESCRIPTION => 'توضیحات بخش چهارم',
            self::ABOUT_US_FOURTH_SECTION_IMAGE => 'تصویر بخش چهارم',


            // ONLINE APP
            self::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS => 'ارسال پیام خودکار بعد از ثبت نوبت',
            self::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE => 'متن پیام',

            // APPLICATION
            self::APP_FULL_APPOINTMENT_HEADER => 'تیتر برای زمانی که نوبت های پزشک پر هست',
            self::APP_FULL_APPOINTMENT_BODY => 'متن برای زمانی که نوبت های پزشک پر هست',

            //REGISTRATION
            self::USER_REGISTER_NATIONAL_CODE_REQUIRED => 'الزامی بودن وارد کردن کد ملی در هنگام ثبت نام',
            self::LOGIN_WITHOUT_OTP => 'ورود بدون تایید شماره موبایل',

            //JIBIT
            self::ACTIVE_JIBIT => 'فعال سازی اعتبار جیبیت',

            default => ''
        };
    }

    public function getDescription()
    {
        return match ($this) {
            self::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL,self::SMS_APPOINTMENT_AFTER_PAYMENT => 'پارامتر ها به ترتیب به شکل زیر باشد:
            <br />  ۱ = نام کاربر
             <br/> ۲ = نام خانوادگی کاربر
             <br/> ۳ = نام پزشک
             <br/> ۴ = نام بخش
             <br/> ۵ = تاریخ نوبت
             <br/> ۶ = ساعت نوبت
             <br/> ۷ = لینک جزئیات
             <br/> ۸ = شماره پیگیری
             ',
            self::SMS_APPROVED_MONITORING_APPOINTMENT => 'در صورت فعال بودن پایش نوبت ، و تغییر وضعیت نوبت به در انتظار پرداخت(تایید نوبت) این پیامک برای کاربر ارسال میشود',
            self::INSTAGRAM_ADDRESS => 'آدرس باید به این صورت وارد شد https://www.instagram.com/shemiranweb/',
            self::WHATSAPP_ADDRESS => 'آدرس باید به این صورت وارد شد https://wa.me/090000000',
            self::FOOTER_ENAMAD => 'ادرس url فقط درج شود نه تگ کامل ',
            self::FOOTER_SAMANDEHI => 'ادرس url فقط درج شود نه تگ کامل ',
            self::SMS_SENDER => 'دیفالت بر روی shsms میباشد',
            self::SMS_PARSSMS_LOGIN_TEXT => 'شامل یک پارامتر که کد ارسالی است میباشد.',
            self::PAYMENT_PARSIAN_TOKEN => '<span class="my-3"></span>',
            self::PAYMENT_SAMAN_TERMINAL_PASS => '<span class="my-3"></span>',
            self::ACTIVE_JIBIT => 'در حال توسعه...',
            self::LOGIN_WITHOUT_OTP => 'اخطار امنیتی: با فعال کردن این ویژگی، هر شخصی میتواند با هر شماره ای وارد سیستم شده و نوبت های مربوط به هر شماره را مشاهده کند!!!!!',
            self::SMS_FARAZ_LINE_NUMBER => 'الزامی برای ارسال توسط فراز و آیپی پنل',
            self::SMS_PARSSMS_ADD_APPOINTMENT_TEXT => 'پارامتر های قابل قرار گیری: <br />  ۱ = نام کاربر
             <br/> ۲ = نام خانوادگی کاربر
             <br/> ۳ = نام پزشک
             <br/> ۴ = نام بخش
             <br/> ۵ = تاریخ نوبت
             <br/> ۶ = ساعت نوبت
             <br/> ۷ = لینک جزئیات
             <br/> ۸ = شماره پیگیری.',
            default => ''
        };
    }

    public function uiDisabled(): bool
    {
        if (disableUi()) {
            return match ($this) {
                self::SITE_TITLE => true,
                self::SITE_SLIDER_TITLE => true,
                self::FOOTER_DESCRIPTION => true,
                self::SITE_FIRST_SECTION_TITLE => true,
                self::SITE_FIRST_SECTION_DESCRIPTION => true,
                self::SITE_SECEND_SECTION_TITLE => true,
                self::SITE_SECEND_SECTION_DESCRIPTION => true,
                default => false
            };
        }
        return false ;
    }
    public function deactiveFeature():bool {
        return match($this) {
            self::ACTIVE_JIBIT => true ,
            default => false ,
        };
    }
    public function render(): string
    {
        return $this->getType()->component($this->value);
    }

    public function getType(): SettingTypeEnum
    {
        return match ($this) {
            self::SITE_LOGO_URL , self::HEADER1_IMAGE => SettingTypeEnum::IMAGE,
            self::DEFAULT_EXERCISE_STATUS => SettingTypeEnum::SELECT,
            self::SMS_SENDER => SettingTypeEnum::SELECT,
            self::PAYMEN_ACTIVE_DRIVER => SettingTypeEnum::SELECT,
            self::APPOINTMENT_MORE_THAT_ONE_PER_DAY , self::HEADER1_SHOW_BUTTONS , self::HEADER1_SHOW_BUTTON1, self::HEADER1_SHOW_BUTTON2 , self::ENABLE_DOCTORS_MENU , self::ENABLE_CONTACT_US_MENU => SettingTypeEnum::CHECK,
            self::APPOINTMENT_SHOW_FALSE_STATUS_DAYS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS => SettingTypeEnum::CHECK,
            self::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER => SettingTypeEnum::CHECK,
            self::SHOW_FALSE_APPOINTMENT_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_FOR_OTHERS_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_DESCRIPTION_STATUS => SettingTypeEnum::CHECK,
            self::SECREYERY_SEND_LINK_FOR_APPOINTMENT => SettingTypeEnum::CHECK,
            self::APPOINTMENT_STATUS => SettingTypeEnum::CHECK,
            self::ACTIVE_JIBIT => SettingTypeEnum::CHECK,
            self::PAYMENT_RULES_AND_CONDITION_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION => SettingTypeEnum::CHECK,
            self::SUPPORT_USER_ROLE , self::ACTIVE_HEADER => SettingTypeEnum::SELECT,
            self::WEIGHT_CHART_DESCRIPTION_APP => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_CANCEL_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::PAYMENT_RULES_AND_CONDITION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND => SettingTypeEnum::TEXTAREA,

            self::CONTACTUS_FIRST_SECTION_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_SHOW_DESCRIPTION_IN_APP_LIST => SettingTypeEnum::CHECK,
            self::ALLOW_MULTIPLE_APP_FROM_ADMIN_PANEL => SettingTypeEnum::CHECK,
            self::CONTACTUS_FIRST_SECTION_BODY => SettingTypeEnum::TEXTAREA,
            self::SITE_FIRST_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::SITE_SECEND_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::CONTACTUS_FORM_STATUS => SettingTypeEnum::CHECK,
            self::SHOW_ABOUT_US_MENU_BUTTON => SettingTypeEnum::CHECK,
            self::CONTACTUS_FORM_ADDRESS => SettingTypeEnum::TEXTAREA,
            self::FOOTER_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT => SettingTypeEnum::TEXTAREA,

            self::ABOUT_US_SECEND_SECTION_IMAGE,self::ABOUT_US_THIRD_SECTION_IMAGE,self::ABOUT_US_FOURTH_SECTION_IMAGE => SettingTypeEnum::IMAGE,

            self::SMS_PRRSSMS_MONITORING_APP,self::SMS_PARSSMS_MONITORING_APPROVED_APP,self::SMS_PARSSMS_MONITORING_DIS_APPROVED_APP => SettingTypeEnum::TEXTAREA,
            self::SMS_PRSSMS_APPOINTMENT_WAITING_PAYMENT => SettingTypeEnum::TEXTAREA,
            self::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_FIRST_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_SECEND_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_THIRD_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_FOURTH_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APP_FULL_APPOINTMENT_BODY => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_GALLERY_BODY, self::FOOTER_ENAMAD ,  self::FOOTER_SAMANDEHI => SettingTypeEnum::TEXTAREA,
            self::SMS_PARSSMS_LOGIN_TEXT, self::SMS_PARSSMS_EDIT_APPOINTMENT_TEXT , self::SMS_PARSSMS_ADD_APPOINTMENT_TEXT, self::SMS_PARSSMS_CANCEL_APPOINTMENT_TEXT ,self::SMS_PARSSMS_REMINDER_TEXT => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_ONLINE_DESCRPTION => SettingTypeEnum::TEXTAREA,
            self::UI_NOW_SHOW_SEARCH_BAR , self::ACTIVE_API, self::ENABLE_CITY_SEARCH , self::ENABLE_LATEST_DOCTORS , self::ENABLE_HOME_FAQ , self::MOST_VIEWED_SECTIONS_ICONS_VIEW, self::ENABLE_MOST_VIEWED_SECTIONS , self::ENABLE_DOCTOR_REGISTRATION , self::SHOW_FLOATING_SOCIAL_ICONS , self::DISABLE_FOOTER_DISPLAY=> SettingTypeEnum::CHECK,
            self::USER_REGISTER_NATIONAL_CODE_REQUIRED => SettingTypeEnum::CHECK,
            self::LOGIN_WITHOUT_OTP => SettingTypeEnum::CHECK,
            self::GO_TO_PAYMENT_DIRECTLY => SettingTypeEnum::CHECK,
            self::DONT_SEND_SMS_FOR_PAYMENT_LINK => SettingTypeEnum::CHECK,
            default => SettingTypeEnum::TEXT
        };
    }

    public function separatorTitle() {
        return match ($this) {
            self::SMS_SENDER => 'تنظیمات ارسال کننده پیامک',
            self::SMS_API_LOGIN_TEMPLATE => 'ورود و ثبت نوبت',
            self::SMS_APPOINTMENT_WAITING_PAYMENT => 'پیامک های مربوط به پرداخت',
            self::SMS_SET_APP_MONITORING => 'پایش نوبت',
            self::SMS_APPOINTMENT_TO_DOCTOR => 'اطلاع رسانی به اپراتور و پزشک',
            self::APPOINTMENT_STATUS => 'تنظیمات عمومی',
            self::APPOINTMENT_DESCRIPTION_STATUS => 'صفحه جزئیات نوبت',
            self::PAYMENT_PAYSTAR_TOKEN => 'درگاه پی استار',
            self::PAYMENT_ZARINPAL_MERCHENID => 'درگاه زرین پال',
            self::PAYMENT_PARSIAN_TOKEN => 'درگاه پارسیان',
            self::PAYMENT_SAMAN_TERMINAL_NUMBER => 'درگاه سامان',
            self::PAYMENT_SEP_TERMINAL_ID => 'درگاه سپ(درگاه سامان کیش)',
            self::PAYMENT_RULES_AND_CONDITION_STATUS => 'شرایط و قوانین پرداخت',
            self::LOGIN_WITHOUT_OTP => 'دارای حساسیت امنیتی',
            self::GO_TO_PAYMENT_DIRECTLY => 'تنظیمات UX',
            default => '' ,
        };
    }
    /**
     * Radio, select and checkbox options
     */
    public function options(): array
    {
        return match ($this) {
            self::ACTIVE_HEADER => [
                'search_header' => 'هدر با سرچ',
                'image_header' => 'هدر با معرفی',
                'ba_image' => 'هدر با عکس پس زمینه',
            ],
            self::SMS_SENDER => [
                'shsms' => 'shsms',
                'ghasedak' => 'قاصدک',
                'parsasms' => 'پارس',
                'farazsms' => 'فراز',
                'ippannel' => 'آی پی پنل',
                'starpayam' => 'استار پیام',
            ],
            self::SUPPORT_USER_ROLE => User::adminSupportRoles(),
            self::PAYMEN_ACTIVE_DRIVER => [
                'zrinpal' => 'زرین پال',
                'parsian'=> 'پارسیان',
                'saman' => 'سامان',
                'sep' => 'سپ'],
            default => []
        };
    }
}
