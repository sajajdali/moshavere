<?php

namespace Modules\Reminder\Enum;

use App\interface\EnumHasNameInterface;

enum ReminderParametersEnum: int implements EnumHasNameInterface
{

    case FIRST_NAME = 1;
    case LAST_NAME = 2;
    case VISIT_DATE = 3;
    case VISIT_TIME = 4;
    case SERVICE_NAME = 5;
    case DOCTOR_NAME = 6;
    case LINK = 7;


    public function getName(): string
    {
        return match ($this) {
            self::FIRST_NAME => 'نام',
            self::LAST_NAME => 'نام خانوادگی',
            self::VISIT_DATE => 'تاریخ نوبت',
            self::VISIT_TIME => 'زمان نوبت',
            self::SERVICE_NAME => 'نام بخش',
            self::DOCTOR_NAME => 'نام پزشک',
            self::LINK => 'لینک نوبت',
            default => "",
        };
    }
}
