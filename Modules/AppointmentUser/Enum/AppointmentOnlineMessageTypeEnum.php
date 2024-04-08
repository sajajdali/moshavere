<?php

namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;
use PhpParser\Node\Expr\Array_;

enum AppointmentOnlineMessageTypeEnum: int implements EnumHasApiResultInterface
{
    case QUESTION = 1;
    case ANSWER = 2;

    public function getName(): string
    {
        return match ($this) {
            self::QUESTION      => 'سوال',
            self::ANSWER   => 'جواب',
        };
    }
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::QUESTION => 'bg-info',
            self::ANSWER   => 'bg-success',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::QUESTION => 'table-info',
            self::ANSWER   => 'table-success',
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
