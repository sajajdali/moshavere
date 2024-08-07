<?php

namespace Modules\User\Enum;

use ReflectionClass;
use Illuminate\Support\Facades\DB;
use App\interface\EnumHasNameInterface;

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
    case NATIONAL_CODE = 18;
    case CITY = 19;
    case FAVORITE_DOCTOR = 20;

        // doctor Enum
    case SPECIALITY_TYPE = 10;
    case DOC_BIOGRAPHY = 11;
    case LICENCE_NUMBER = 12;
    case DOC_ADDRESS = 13;
    case DOCTOR_ORDER = 14;
    case ACTIVE_APPOINTMENT = 15;
    case BAN_USER = 16;
    case DR_GALLERY = 17;
    case DR_BANNER = 21;
    case DR_ENEMRGENCY_STATUS = 22;
    case DR_ENEMRGENCY_ORDER = 23;
    case DR_INFO_STATUS = 24;
    case DR_INFO_ORDER = 25;
    case DR_WAITING_TIME = 26;
    case DR_WEBSITE_DISPLAY_MOBILE = 27;
    case DR_WEBSITE_DISPLAY_NAVIGATION = 28;
    case DR_WEBSITE_DISPLAY_ADDRESS = 29;
    case DR_WEBSITE_DISPLAY_EXPERINCE = 30;
    case DR_WEBSITE_DISPLAY_DESCRIPTION = 31;
    case DR_REGISTRATION_DESCRIPTION = 32;
    case DR_REGISTRATION_FROM = 33; // SELF or ADMIN




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
            self::NATIONAL_CODE => 'کد ملی',
            self::CITY => 'شهر',
        };
    }
    public static function fromOldKey(string $oldKey, $userid): ?self
    {
        // Mapping old string keys to enum cases
        $map = [
            'FIRST_NAME' => self::FIRST_NAME,
            'LAST_NAME' => self::LAST_NAME,
            'AVATAR' => self::AVATAR,
        ];
        return $map[$oldKey] ?? null;
    }
    
}
