<?php

namespace Modules\AppointmentSetting\app\Enum;

use App\interface\EnumHasDefaultInterface;

enum AppintmentSettingDayNumber: int implements EnumHasDefaultInterface
{
    case SATURDAY = 0;
    case SUNDAY = 1;
    case MONDAY = 2;
    case TUESDAY = 3;
    case WEDNESDAY = 4;
    case THURSDAY = 5;
    case FRIDAY = 6;
    public static function getDefault(): EnumHasDefaultInterface
    {
        return self::SATURDAY;
    }
    public static function getConstant(string $name): ?AppintmentSettingDayNumber {
        switch ($name) {
            case 'saturday':
                return self::SATURDAY;
            case 'sunday':
                return self::SUNDAY;
            case 'monday':
                return self::MONDAY;
            case 'tuesday':
                return self::TUESDAY;
            case 'wednesday':
                return self::WEDNESDAY;
            case 'thursday':
                return self::THURSDAY;
            case 'friday':
                return self::FRIDAY;
            default:
                return null;
        }
    }


    public  function getName()
    {
        return match ($this) {
            self::SATURDAY  => 'شنبه',
            self::SUNDAY    => 'یکشنبه',
            self::MONDAY    => 'دوشنبه',
            self::TUESDAY   => 'سه شنبه',
            self::WEDNESDAY => 'چهارشنبه',
            self::THURSDAY  => 'پنجشنبه',
            self::FRIDAY    => 'جمعه',
        };
    }
}
