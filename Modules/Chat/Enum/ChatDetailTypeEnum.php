<?php

namespace Modules\Chat\Enum;

use App\interface\EnumHasNameInterface;
use App\trait\EnumFunctionTrait;

enum ChatDetailTypeEnum : int
{
    use EnumFunctionTrait;
    case MESSAGE = 1;
    case ADMIN_MESSAGE = 2;


    public function getName(): string
    {
        return match ($this) {
            self::MESSAGE => 'سوال کاربر',
            self::ADMIN_MESSAGE => 'پاسخ مدیر',
        };
    }

    public function apiResult(): array
    {
        return [
            'name' => $this->value,
            'body' => $this->getName()
        ];
    }
}
