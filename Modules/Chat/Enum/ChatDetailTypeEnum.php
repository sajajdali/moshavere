<?php

namespace Modules\Chat\Enum;

use App\interface\EnumHasNameInterface;
use App\trait\EnumFunctionTrait;

enum ChatDetailTypeEnum : int
{
    use EnumFunctionTrait;
    case MESSAGE = 0;
    case ADMIN_MESSAGE = 2;
    case ATTACH = 10;


    public function getName(): string
    {
        return match ($this) {
            self::MESSAGE => 'text',
            self::ADMIN_MESSAGE => 'text',
            self::ATTACH => 'file',
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
