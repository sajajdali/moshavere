<?php

namespace Modules\Setting\Enum;

use App\interface\EnumHasNameInterface;
use Modules\Exercise\Entities\ExercisePlanRequest;
use Modules\Setting\Interface\SettingHasCacheInterface;
use Modules\Setting\Interface\SettingHasOptionInterface;
use Modules\Setting\Interface\SettingRenderAbleInterface;
use Modules\Setting\Interface\SettingTypeInterface;
use Modules\User\Entities\User;

enum SettingKeyEnum: int implements EnumHasNameInterface, SettingTypeInterface, SettingHasCacheInterface, SettingRenderAbleInterface, SettingHasOptionInterface
{
    case DEFAULT_EXERCISE_STATUS = 1;
    case SITE_LOGO_URL = 2;
    case SITE_TITLE = 3;
    case APPOINTMENT_STATUS = 4;
    case APPOINTMENT_DESCRIPTION_STATUS = 5;
    case APPOINTMENT_DESCRIPTION = 6;
    case APPOINTMENT_CANCEL_DESCRIPTION = 7;
    case APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION = 8;
    case APPOINTMENT_DEADLINE_VIA_ADMIN = 9;
    case APPOINTMENT_DEADLINE_VIA_USER = 10;
    case SITE_SLIDER_TITLE = 11;
    case APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND = 12 ;
    case APPOINTMENT_FOR_OTHERS_STATUS = 13 ;

        //sms
    case SMS_API_TOKEN = 20;
    case SMS_API_LOGIN_TEMPLATE = 30;
    case SMS_APPOINTMENT_RECEIVING_SUCCESSFUL = 31;
    case SMS_APPOINTMENT_WAITING_PAYMENT = 32;
    case SMS_APPOINTMENT_AFTER_PAYMENT = 33;
    case SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT = 34;
    case SMS_APPOINTMENT_TIME_UPDATE = 35;
    case SMS_APPOINTMENT_CANCEL = 36;
    case SMS_APPOINTMENT_TO_DOCTOR = 37;
    case SMS_APPOINTMENT_TO_OPERATOR = 38;
    case SMS_APPROVED_MONITORING_APPOINTMENT = 39;
    case SMS_DIS_APPROVED_MONITORING_APPOINTMENT = 40;
    case SMS_FEEDBACK = 41;
    case SUPPORT_USER_ROLE = 100;

        //payment
    case PAYMENT_PAYSTAR_STATUS = 152;
    case PAYMENT_PAYSTAR_TOKEN = 150;
    case PAYMENT_PAYSTAR_SIGN = 151;
    case PAYMENT_ZARINPAL_STATUS = 153;
    case PAYMENT_ZARINPAL_MERCHENID = 154;
    case PAYMENT_RULES_AND_CONDITION_STATUS = 155;
    case PAYMENT_RULES_AND_CONDITION_DESCRIPTION = 156;
    case SECREYERY_SEND_LINK_FOR_APPOINTMENT = 157;


    case WEIGHT_CHART_DESCRIPTION_APP = 120;
    case VOIP_USERNAME = 160;
    case VOIP_PASSWORD = 170;



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
            self::SITE_SLIDER_TITLE    => 'عنوان در ابتتدای صفحه ای اصلی و بالای قسمت جست و جو',
            self::APPOINTMENT_DESCRIPTION_IN_CHECKOUT_PAGE_REFUND    => 'توضیحات در صفحه قبل از تایید نوبت توس کاربر(checkout)',


            self::SMS_API_TOKEN => 'توکن API پیامک',
            self::DEFAULT_EXERCISE_STATUS => 'وضعیت برنامه بعد از تجویز',
            self::SUPPORT_USER_ROLE => 'گروه کاربری پشتیبانان',
            self::SMS_API_LOGIN_TEMPLATE => 'الگو پیامک ورود',
            self::WEIGHT_CHART_DESCRIPTION_APP => 'متن توضیح در صفحه ی مشاهده مودار وزنی ',

            //payment
            self::PAYMENT_PAYSTAR_STATUS => 'فعال بودن درگاه پی استار',
            self::PAYMENT_PAYSTAR_TOKEN => 'کد درگاه پرداخت پی استار',
            self::PAYMENT_PAYSTAR_SIGN => 'امضا درگاه پی استار',
            self::PAYMENT_ZARINPAL_STATUS => 'فعال بودن درگاه زرین پال',
            self::PAYMENT_ZARINPAL_MERCHENID => 'مرچند ایدی درگاه زرین پال',
            self::PAYMENT_RULES_AND_CONDITION_STATUS => 'فعال سازی شرایط و قوانین پرداخت',
            self::PAYMENT_RULES_AND_CONDITION_DESCRIPTION => 'شرایط و قوانین مربوط به پرداخت',
            self::SECREYERY_SEND_LINK_FOR_APPOINTMENT => 'امکان ارسال لینک پرداخت نوبت به کاربر توسط منشی',

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

    public function render(): string
    {
        return $this->getType()->component($this->value);
    }

    public function getType(): SettingTypeEnum
    {
        return match ($this) {
            self::DEFAULT_EXERCISE_STATUS => SettingTypeEnum::SELECT,
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
            default => []
        };
    }
}
