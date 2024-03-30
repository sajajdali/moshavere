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
    case SUPPORT_USER_ROLE = 100;
    case PAYMENT_PAYSTAR_TOKEN = 150;
    case PAYMENT_PAYSTAR_SIGN = 151;
    case WEIGHT_CHART_DESCRIPTION_APP = 120;

    public function isSupportCache(): bool
    {
        return match ($this) {
            self::SMS_API_TOKEN => false,
            self::SUPPORT_USER_ROLE => false,
            self::SMS_API_LOGIN_TEMPLATE => false,
            self::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL => false,
            self::SMS_APPOINTMENT_WAITING_PAYMENT => false,
            self::SMS_APPOINTMENT_AFTER_PAYMENT => false,
            self::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT => false,
            self::SMS_APPOINTMENT_TIME_UPDATE => false,
            self::SMS_APPOINTMENT_CANCEL => false,
            self::SMS_APPOINTMENT_TO_DOCTOR => false,
            self::SMS_APPOINTMENT_TO_OPERATOR => false,
            self::PAYMENT_PAYSTAR_TOKEN => false,
            self::PAYMENT_PAYSTAR_SIGN => false,
            self::WEIGHT_CHART_DESCRIPTION_APP => false,
            default => true
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::SMS_API_TOKEN => 'توکن API پیامک',
            self::DEFAULT_EXERCISE_STATUS => 'وضعیت برنامه بعد از تجویز',
            self::SUPPORT_USER_ROLE => 'گروه کاربری پشتیبانان',
            self::SMS_API_LOGIN_TEMPLATE => 'الگو پیامک ورود',
            self::PAYMENT_PAYSTAR_TOKEN => 'کد درگاه پرداخت پی استار',
            self::PAYMENT_PAYSTAR_SIGN => 'امضا درگاه پی استار',
            self::WEIGHT_CHART_DESCRIPTION_APP => 'متن توضیح در صفحه ی مشاهده مودار وزنی ',
            // sms
            self::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL => 'پیامک به کاربر پس از دریافت نوبت موفق',
            self::SMS_APPOINTMENT_WAITING_PAYMENT => 'پیامک به کاربر در صورتی که پرداخت فعال باشد و نوبت برای کاربر ثبت شود (نوبتی که نیاز به پرداخت دارد)',
            self::SMS_APPOINTMENT_AFTER_PAYMENT => 'پیامک به کاربر پس از پرداخت موفق هزینه',
            self::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT => 'پیامک به کاربر در صورتی که پرداخت نکند و نوبت وی حذف شود',
            self::SMS_APPOINTMENT_TIME_UPDATE => 'پیامک به کاربر پس از ویرایش زمان نوبت',
            self::SMS_APPOINTMENT_CANCEL => 'پیامک به کاربر پس از کنسل شدن نوبت',
            self::SMS_APPOINTMENT_TO_DOCTOR => 'پیامک به پزشک پز از دریافت نوبت توسط هر کاربر',
            self::SMS_APPOINTMENT_TO_OPERATOR => 'پیامک به اپراتور پس از دریافت هر نوبت',
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
            self::SMS_API_TOKEN => SettingTypeEnum::TEXT,
            self::DEFAULT_EXERCISE_STATUS => SettingTypeEnum::SELECT,
            self::SUPPORT_USER_ROLE => SettingTypeEnum::SELECT,
            self::PAYMENT_PAYSTAR_TOKEN => SettingTypeEnum::TEXT,
            self::PAYMENT_PAYSTAR_SIGN => SettingTypeEnum::TEXT,
            self::WEIGHT_CHART_DESCRIPTION_APP => SettingTypeEnum::TEXTAREA,
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
