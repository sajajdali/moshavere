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
    case DIET_TYPE = 6;
    case BIRTHDAY = 7;
    case TALL = 8;
    case WEIGHT = 9;
    case TARGET_WEIGHT = 10;
    case TARGET_PLAN = 11;
    case BODY_FAT = 12;
    case DAILY_WATER_CONSUMPTION = 13;
    case BODY_PHYSICAL_STYLE = 14;
    case BODY_STYLE = 15;
    case TYPE_DAILY_WORK = 16;
    case DISEASES = 17;
    case FOOD_RESTRICTION = 18;
    case HABITS = 19;
    case WAKE_UP = 20;
    case HOW_MUCH_EXPERIENCE_SPORTS = 21;
    case TARGET_OF_EXERCISE = 22;
    case ACTIVITY_PER_WEEK = 23;
    case HOW_MANY_DAYS_WEEK_EXERCISE = 24;
    case ROUTE = 25;
    case DIET_PLAN = 26;
    case WEAKNESSES_BODY = 27;


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
            self::DIET_TYPE => 'هدف کلی',
            self::BIRTHDAY => 'تاریخ تولد',
            self::TALL => 'قد',
            self::WEIGHT => 'وزن',
            self::TARGET_WEIGHT => 'وزن هدف',
            self::TARGET_PLAN => 'روش رسیدن به هدف',
            self::DIET_PLAN => 'رژیم انتخابی',
            self::BODY_FAT => 'درصد چربی',
            self::DAILY_WATER_CONSUMPTION => 'میزن مصرف مایعات',
            self::BODY_PHYSICAL_STYLE => 'استایل بدنی',
            self::BODY_STYLE => 'استایل بدنی',
            self::TYPE_DAILY_WORK => 'نوع کار روزانه',
            self::DISEASES => 'بیماری ها',
            self::FOOD_RESTRICTION => 'محدودیت غذایی',
            self::HABITS => 'عادت های اشتباه',
            self::WAKE_UP => 'میزان خواب',
            self::HOW_MUCH_EXPERIENCE_SPORTS => 'چند وقت ورزش میکنید',
            self::TARGET_OF_EXERCISE => 'هدف شما از ورش کردن',
            self::ACTIVITY_PER_WEEK => 'میزان فعالیت شما در هفته',
            self::HOW_MANY_DAYS_WEEK_EXERCISE => 'چند روز در هفته ورزش میکنید',
            self::WEAKNESSES_BODY => 'نقاط ضعف بدن',
        };
    }
    public function getOptionName($value): string
    {
        return $this->getOptions()[$value] ?? $value ?? "";
    }

    public  function getOptions(): ?array
    {
        return match ($this) {
            self::GENDER => ['0' => 'مرد', '1' => 'زن'],
            self::DIET_TYPE => ['0' => 'کاهش وزن' ,'1' => 'افزایش وزن','2' => 'تثبیت وزن'],
            self::TARGET_PLAN => ['10' => 'رژیم' ,'20' => 'ورزش','30' => 'رژیم + ورزش'],
            self::BODY_PHYSICAL_STYLE => ['0' => 'اکنومرف', '1' => 'مزومرف' , '2' => 'اندومرف'],
            self::TYPE_DAILY_WORK => ['0' => 'اکثر مواقع نشته ام', '1' => 'اکثر مواقع ایستاده ام' , '2' => 'در خانه کار میکنم', '3' => 'سیار فعال هستم'],
            self::DAILY_WATER_CONSUMPTION => ['0' => 'فقط چای و قهوه', '1' => '۲ تا ۴ لیوان آب' , '2' => '۴ تا ۶ لیوان آب', '3' => ' بیشتر از ۶ لیوان'],
            self::FOOD_RESTRICTION => ['0' => 'گیاه خواری', '1' => 'وگان' , '2' => 'بدون لاکتوز', '3' => 'بدون ماهی' , '4' => 'من تقریبا همه چیز میخورم'],
            self::WEAKNESSES_BODY => ['0' => 'سینه ها', '1' => 'بازو ها' , '2' => 'پاهای لاغر', '3' => 'شکم چاق' , '4' => ' هیچ کدام از مشکالات بالا را ندارم'],
            self::HABITS => ['0' => 'دخانیات', '1' => 'نوشابه' , '2' => 'غذای شور', '3' => 'غذای چرب' , '4' => ' الکل' , '5' => 'شیرینی جات' , '6' => 'شب بیداری' , '7' => 'هیچدام را استفاده نمیکنم'],
            self::WAKE_UP => ['0' => ' کمتر از ۵', '1' => 'بین ۵ تا ۷' , '2' => ' بین ۷ تا ۸', '3' => 'بیشتر از ۸'],
            self::HOW_MUCH_EXPERIENCE_SPORTS => ['0' => 'تازه میخوام شروع کنم', '1' => 'کمتر از ۳ ماه' , '2' => 'بیشتر از ۳ ماه'],
            self::TARGET_OF_EXERCISE => ['0' => 'کات و تفکیک عضلات', '1' => 'افزایش توده عضلات' , '2' => 'افزایش قدرت و حجم عضلات'],
            self::ACTIVITY_PER_WEEK => ['0' =>  'فعالیت فیزیکی بسیار کم', '1' => 'فعالیت فیزیکی کم' , '2' => 'فعالیت فیزیکی متوسط' , '3' => ' فعالیت فیزیکی زیاد' , '4' =>  'فعالیت فیزیکی بسیار زیاد'],
            self::HOW_MANY_DAYS_WEEK_EXERCISE => ['0' => '۱ بار', '1' => ' ۲ بار' , '2' => ' ۳ بار' , '3' => ' ۴ بار' , '4' =>  '۵ بار' , '6' => ' ۶ بار'] ,
            self::DIET_PLAN => ['0' => ' کاهش وزن', '1' => 'افزایش وزن' , '2' => 'تثبیت وزن'] ,
            self::DISEASES => [ '' =>'بدون مقدار'] ,
            default => null
        };
    }
}
