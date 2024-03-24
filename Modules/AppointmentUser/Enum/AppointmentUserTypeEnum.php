<?php

namespace Modules\AppointmentUser\Enum;
use App\interface\EnumHasNameInterface;

enum AppointmentUserTypeEnum: int implements EnumHasNameInterface
{
    case MAIN__APPOINTMENT = 1;
    case BETWEEN_PATIENTS = 2;

    public function getName(): string
    {
        return match($this) {
            self::MAIN__APPOINTMENT => 'اصلی',
            self::BETWEEN_PATIENTS => 'بین مریض',
        };
    }
}
