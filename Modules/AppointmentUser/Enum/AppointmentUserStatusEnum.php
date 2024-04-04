<?php

namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;
use PhpParser\Node\Expr\Array_;

enum AppointmentUserStatusEnum: int implements EnumHasApiResultInterface
{
    case STATUS_PENDING = 0;
    case STATUS_SUCCESSFUL = 1;
    case STATUS_WAIT_PAYMENT = 2;
    case STATUS_CANCEL = 3;
    case STATUS_ATTENDED = 4;
    case STATUS_NOT_ATTENDED = 5;

    public function getName(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'در انتظار',
            self::STATUS_SUCCESSFUL   => 'تایید شده',
            self::STATUS_WAIT_PAYMENT => 'منتظر پرداخت',
            self::STATUS_CANCEL       => 'کنسل شده',
            self::STATUS_ATTENDED     => 'حضور پیدا کرده',
            self::STATUS_NOT_ATTENDED => 'عدم حضور',
        };
    }
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'bg-warning',
            self::STATUS_SUCCESSFUL   => 'bg-success',
            self::STATUS_WAIT_PAYMENT => 'bg-info',
            self::STATUS_CANCEL       => 'bg-danger',
            self::STATUS_ATTENDED     => 'bg-secondary',
            self::STATUS_NOT_ATTENDED => 'bg-primary',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::STATUS_CANCEL         => 'table-danger',
            self::STATUS_PENDING        => 'table-warning',
            self::STATUS_SUCCESSFUL     => 'table-success',
            self::STATUS_WAIT_PAYMENT   => 'table-primary',
        };
    }

    public static function confirmed() : array
    {
        return [
            self::STATUS_PENDING->value,
            self::STATUS_SUCCESSFUL->value,
            self::STATUS_WAIT_PAYMENT->value,
            self::STATUS_ATTENDED->value,
            self::STATUS_NOT_ATTENDED->value,
        ];
    }

    public function apiResult(): array
    {
        return [
            'name' => $this->value,
            'body' => $this->getName()
        ];
    }
}
