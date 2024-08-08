<?php

namespace App\Enum;

use App\interface\EnumHasDefaultInterface;

enum RouteEnum: string implements EnumHasDefaultInterface
{
    case APPOINTMENT = '/appointment/{id}';
    case transaction = '/transaction/show/{id}';


    public static function getDefault(): EnumHasDefaultInterface
    {
        return self::APPOINTMENT;
    }

    public function getLink() {
        return match($this) {
            self::APPOINTMENT => '',
            self::transaction => '',
        };
    }
}
