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
