<?php

namespace Modules\Setting\Enum;

use Modules\User\Entities\User;
use Shetabit\Payment\Facade\Payment;
use App\interface\EnumHasNameInterface;
use Modules\Exercise\Entities\ExercisePlanRequest;
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
    case  SMS_AFTER_REFUND = 74;
    case  SMS_FOR_SEND_MESSAGE_IN_CHATS = 76;

        //payment
    case PAYMENT_PAYSTAR_STATUS = 152;
    case PAYMENT_PAYSTAR_TOKEN = 150;
    case PAYMENT_PAYSTAR_SIGN = 151;
    case PAYMENT_ZARINPAL_STATUS = 153;
    case PAYMENT_ZARINPAL_MERCHENID = 154;
    case PAYMENT_RULES_AND_CONDITION_STATUS = 155;
    case PAYMENT_RULES_AND_CONDITION_DESCRIPTION = 156;
    case SECREYERY_SEND_LINK_FOR_APPOINTMENT = 157;
    case PAYMEN_ACTIVE_DRIVER = 158;


    case WEIGHT_CHART_DESCRIPTION_APP = 120;
    case VOIP_USERNAME = 160;
    case VOIP_PASSWORD = 170;

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
            self::SITE_TITLE    => 'عنوان سایت',
            self::APPOINTMENT_STATUS    => 'فعال بودن نوبت دهی',
            self::APPOINTMENT_FOR_OTHERS_STATUS    => 'امکان ثبت نوبت برای دیگران',
            self::APPOINTMENT_DESCRIPTION_STATUS    => 'فعال بودن توضیحات در صفحه جزئیات نوبت',
            self::APPOINTMENT_DESCRIPTION    => 'توضیحات مربوط به صفحه جزئیات نوبت',
            self::APPOINTMENT_CANCEL_DESCRIPTION    => 'توضیحات مربوط به کنسلی نوبت',
            self::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION    => 'فعال بودن ثبت حضور و یا عدم حضور بیمار',
            self::APPOINTMENT_DEADLINE_VIA_ADMIN    => 'مدت زمان رزرو بودن نوبت برای پرداخت در زمانی که وضعیت نوبت در انتظار پرداخت میباشد و نوبت از طریق پنل ادمین ثبت شده باشد (ساعت)',
            self::APPOINTMENT_DEADLINE_VIA_USER    => 'مدت زمان رزرو بودن نوبت برای پرداخت در زمانی که وضعیت نوبت در انتظار پرداخت میباشد و نوبت را بیمار دریافت کرده باشد(ساعت)',
            self::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER    => 'ثبت نوبت با شماره پرونده در پنل منشی',
            self::SITE_SLIDER_TITLE    => 'عنوان در ابتدای صفحه ای اصلی و بالای قسمت جست و جو',
            self::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND    => 'توضیحات در صفحه قبل از تایید نوبت توس کاربر(checkout)',
            self::APPOINTMENT_MORE_THAT_ONE_PER_DAY    => 'امکان رزرو بیشتر از یک نوبت در هر روز برای هر بیمار',
            self::FOOTER_DESCRIPTION    => 'توضیحات در فورتر سایت',
            self::INSTAGRAM_ADDRESS    => 'ادرس صفحه ی ابنتساگرام شما به صورت :https://www.instagram.com/shemiranweb/ ',
            self::TELEGRAM_ADDRESS    => 'ادرس تلگرام شما ',
            self::SHOW_FALSE_APPOINTMENT_STATUS    => 'نمایش ساعت های پر شده در لیست ساعت ها به کاربران',
            self::SITE_FIRST_SECTION_TITLE    => 'عنوان بخش اول در صفحه ی اصلی(عنوان پیشنهادی: ویزیت فوری)',
            self::SITE_FIRST_SECTION_DESCRIPTION    => 'توضیح بخش اول در صفحه ی اصلی',
            self::SITE_SECEND_SECTION_TITLE    => 'عنوان بخش دوم در صفحه ی اصلی(عنوان پیشنهادی: معرفی پزشکان)',
            self::SITE_SECEND_SECTION_DESCRIPTION    => 'توضیح بخش دوم در صفحه ی اصلی)',
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS    => ' وضعیت توضیحات در صفحه ی جزئیات نوبت که مربوط به پرداخت میباشد',
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT    => 'متن توضیحات در صفحه ی جزئیات نوبت که مربوط به پرداخت میباشد ',

            // SMS
            self::SMS_API_TOKEN => 'توکن API پیامک',
            self::DEFAULT_EXERCISE_STATUS => 'وضعیت برنامه بعد از تجویز',
            self::SUPPORT_USER_ROLE => 'گروه کاربری پشتیبانان',
            self::SMS_API_LOGIN_TEMPLATE => 'الگو پیامک ورود',
            self::WEIGHT_CHART_DESCRIPTION_APP => 'متن توضیح در صفحه ی مشاهده مودار وزنی ',
            self::SMS_AFTER_REFUND => 'نام الگوی پیامکی، بعد از استرداد وجه',
            self::SMS_FOR_SEND_MESSAGE_IN_CHATS => 'نام الگوی پیامکی، بعد از پاسخ دادن به چت',

            //payment
            self::PAYMENT_PAYSTAR_STATUS => 'فعال بودن درگاه پی استار',
            self::PAYMENT_PAYSTAR_TOKEN => 'کد درگاه پرداخت پی استار',
            self::PAYMENT_PAYSTAR_SIGN => 'امضا درگاه پی استار',
            self::PAYMENT_ZARINPAL_STATUS => 'فعال بودن درگاه زرین پال',
            self::PAYMENT_ZARINPAL_MERCHENID => 'مرچند ایدی درگاه زرین پال',
            self::PAYMENT_RULES_AND_CONDITION_STATUS => 'فعال سازی شرایط و قوانین پرداخت',
            self::PAYMENT_RULES_AND_CONDITION_DESCRIPTION => 'شرایط و قوانین مربوط به پرداخت',
            self::SECREYERY_SEND_LINK_FOR_APPOINTMENT => 'امکان ارسال لینک پرداخت نوبت به کاربر توسط منشی',
            self::PAYMEN_ACTIVE_DRIVER => 'درگاه فعال',

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

            // voip
            self::VOIP_USERNAME => 'نام کاربری برای API ',
            self::VOIP_PASSWORD => 'کلمه عبور برای API ',

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
            default => ''
        };
    }

    public function getDescription()
    {
        return match ($this) {
            self::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL => 'پارامتر ها به ترتیب به شکل زیر باشد:
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

    public function render(): string
    {
        return $this->getType()->component($this->value);
    }

    public function getType(): SettingTypeEnum
    {
        return match ($this) {
            self::DEFAULT_EXERCISE_STATUS => SettingTypeEnum::SELECT,
            self::PAYMEN_ACTIVE_DRIVER => SettingTypeEnum::SELECT,
            self::APPOINTMENT_MORE_THAT_ONE_PER_DAY => SettingTypeEnum::CHECK,
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS => SettingTypeEnum::CHECK,
            self::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_SET_APPOINTMENT_WITH_DOCUMENT_NUMBER => SettingTypeEnum::CHECK,
            self::SHOW_FALSE_APPOINTMENT_STATUS => SettingTypeEnum::CHECK,
            self::PAYMENT_PAYSTAR_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_FOR_OTHERS_STATUS => SettingTypeEnum::CHECK,
            self::PAYMENT_ZARINPAL_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_DESCRIPTION_STATUS => SettingTypeEnum::CHECK,
            self::SECREYERY_SEND_LINK_FOR_APPOINTMENT => SettingTypeEnum::CHECK,
            self::APPOINTMENT_STATUS => SettingTypeEnum::CHECK,
            self::PAYMENT_RULES_AND_CONDITION_STATUS => SettingTypeEnum::CHECK,
            self::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION => SettingTypeEnum::CHECK,
            self::SUPPORT_USER_ROLE => SettingTypeEnum::SELECT,
            self::WEIGHT_CHART_DESCRIPTION_APP => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_CANCEL_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::PAYMENT_RULES_AND_CONDITION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND => SettingTypeEnum::TEXTAREA,

            self::CONTACTUS_FIRST_SECTION_STATUS => SettingTypeEnum::CHECK,
            self::CONTACTUS_FIRST_SECTION_BODY => SettingTypeEnum::TEXTAREA,
            self::SITE_FIRST_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::SITE_SECEND_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::CONTACTUS_FORM_STATUS => SettingTypeEnum::CHECK,
            self::CONTACTUS_FORM_ADDRESS => SettingTypeEnum::TEXTAREA,
            self::FOOTER_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT => SettingTypeEnum::TEXTAREA,

            self::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_FIRST_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_SECEND_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_THIRD_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            self::ABOUT_US_FOURTH_SECTION_DESCRIPTION => SettingTypeEnum::TEXTAREA,
            default => SettingTypeEnum::TEXT
        };
    }

    /**
     * Radio, select and checkbox options
     */
    public function options(): array
    {
        return match ($this) {
            self::SUPPORT_USER_ROLE => User::adminSupportRoles(),
            self::PAYMEN_ACTIVE_DRIVER => ['zrinpal' => 'zrinpal'],
            default => []
        };
    }
}
