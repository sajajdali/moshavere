<?php

namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;

enum AppointmentOnlineMessageSeenEnum: int implements EnumHasApiResultInterface
{
    case SEEN = 0;
    case UNSEEN = 1;

    public function getName(): string
    {
        return match ($this) {
            self::SEEN      => 'دیده شده',
            self::UNSEEN   => 'دیده نشده',
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
