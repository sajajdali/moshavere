<?php

namespace Modules\Reminder\Enum;

use App\interface\EnumHasNameInterface;
use App\trait\EnumFunctionTrait;

enum ReminderStatusEnum : int implements EnumHasNameInterface
{
    use EnumFunctionTrait ;

    case SMS = 1;
    case NOTIFICATION = 2;
    case CALL = 3;


    public function getName(): string
    {
        return match($this)
        {
            self::SMS => 'پیامک' ,
            self::NOTIFICATION => 'ناتیفیکیشن موبایل' ,
            self::CALL => 'تماس' ,
            default => "",
        } ;
    }
}
