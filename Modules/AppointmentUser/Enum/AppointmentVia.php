<?php

namespace Modules\AppointmentUser\Enum;
use App\interface\EnumHasApiResultInterface;
use App\interface\EnumHasNameInterface;

enum AppointmentVia: int implements EnumHasNameInterface , EnumHasApiResultInterface
{
    case SELF = 1;
    case BY_ADMIN = 2;
    case IMPORT_FROM = 3;

    public function getName(): string
    {
        return match($this) {
            self::SELF => 'توسط بیمار',
            self::BY_ADMIN => 'توسط ادمین',
            self::IMPORT_FROM => 'ایمپورت شده',
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
