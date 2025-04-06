<?php

namespace Modules\AppointmentSetting\app\Enum;

use App\interface\EnumHasDefaultInterface;

enum AppintmentSettingInterface: int implements EnumHasDefaultInterface
{
    case CHECK = 1;
    case DONT_CHECK = 0;


    public static function getDefault(): EnumHasDefaultInterface
    {
        return self::CHECK;
    }
    public  function getName() {
        return match($this) {
            self::CHECK => 'بررسی بشود',
            self::DONT_CHECK => 'بررسی نشود',
        };
    }

    public function getBadge() {
        return match($this) {
            self::CHECK => '<span class="badge bg-success rounded-pill">بررسی بشود</span>',
            self::DONT_CHECK => '<span class="badge bg-danger rounded-pill">بررسی نشود</span>',
        };
    }
}
