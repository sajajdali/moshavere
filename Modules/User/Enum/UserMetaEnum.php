<?php

namespace Modules\User\Enum;

use App\interface\EnumHasNameInterface;
use ReflectionClass;

enum UserMetaEnum: int implements EnumHasNameInterface
{
    case FIRST_NAME = 1;
    case LAST_NAME = 2;
    case AVATAR = 3;
    case CREATOR = 4;
    case GENDER = 5;
    case BIRTHDAY = 6;
    case DISEASES = 7;
    case DOCUMENT_NUMBER = 8;
    case MOBILE = 9;


    public static function keys(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }

    public function getName(): string
    {
        return match ($this) {
            self::FIRST_NAME => 'نام',
            self::LAST_NAME => 'نام خانوادگی',
            self::AVATAR => 'عکس پروفایل',
            self::GENDER => 'جنسیت',
            self::BIRTHDAY => 'تاریخ تولد',
            self::DISEASES => 'بیماری ها',
            self::DOCUMENT_NUMBER => 'شماره پرونده',
        };
    }


}
