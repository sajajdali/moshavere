<?php

namespace App\Enum;

use App\interface\EnumHasDefaultInterface;

enum ActiveEnum: int implements EnumHasDefaultInterface
{
    case ACTIVE = 1;
    case DEACTIVE = 0;


    public static function getDefault(): EnumHasDefaultInterface
    {
        return self::ACTIVE;
    }
    public  function getName() {
        return match($this) {
            self::ACTIVE => 'فعال',
            self::DEACTIVE => 'غیرفعال',
        };
    }

    public function getBadge() {
        return match($this) {
            self::ACTIVE => '<span class="badge bg-success rounded-pill">فعال</span>',
            self::DEACTIVE => '<span class="badge bg-danger rounded-pill">غیرفعال</span>',
        };
    }
    public function getBtnColor() {
        return match($this) {
            self::ACTIVE => 'btn-success',
            self::DEACTIVE => 'btn-danger',
        };
    }
}
