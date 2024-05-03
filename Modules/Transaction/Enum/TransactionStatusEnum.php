<?php

namespace Modules\Transaction\Enum;

use App\interface\EnumHasNameInterface;
use App\trait\EnumFunctionTrait;
use ReflectionClass;

enum TransactionStatusEnum : int implements EnumHasNameInterface
{
    use EnumFunctionTrait ;

    case ALL = -1;
    case SUCCESSFUL = 1;
    case REJECTED = 0;
    case PENDING = 2;


    public function getName(): string
    {
        return match($this)
        {
            self::ALL => 'همه' ,
            self::SUCCESSFUL => 'موفق' ,
            self::REJECTED => 'ناموفق' ,
            self::PENDING => 'در حال انجام' ,
            default => "",
        } ;
    }

    public function getColor(): string
    {
        return match($this)
        {
            self::ALL => '#7143BD' ,
            self::SUCCESSFUL => '#006D44' ,
            self::REJECTED => '#BE003A' ,
            self::PENDING => '#FDBB21' ,
            default => "#7143BD",
        } ;
    }

    public function apiResult(): array
    {
        return [
            'name' => $this->value,
            'body' => $this->getName()
        ];
    }

    public static function all()
    {
        $reflection = new ReflectionClass(__CLASS__);
        $res = [];
        foreach ($reflection->getConstants() as $key => $value){
            $res[] = [
                'id' => $value->value,
                'name' => self::tryFrom($value->value)->getName(),
                'color' => self::tryFrom($value->value)->getColor()
            ];
        }
        sort($res);
        return $res;
    }
}
