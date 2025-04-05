<?php

namespace Modules\User\Enum;

use App\interface\EnumHasNameInterface;
use ReflectionClass;

enum UserSpecialityType: int implements EnumHasNameInterface
{
    case DOCTOR = 1;
    case OPERATOR = 2;
    case SECRETARY = 3;


    public function getName(): string
    {
        return match ($this) {
            self::DOCTOR => 'پزشک',
            self::OPERATOR => 'اپراتور',
            self::SECRETARY => 'منشی',
        };
    }
}
