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
    case STATUS_DISAPPROVED = 6;
    case STATUS_MONITORING = 7;

    public function getName(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'در انتظار',
            self::STATUS_SUCCESSFUL   => 'تایید شده',
            self::STATUS_WAIT_PAYMENT => 'منتظر پرداخت',
            self::STATUS_CANCEL       => 'کنسل شده',
            self::STATUS_ATTENDED     => 'حضور پیدا کرده',
            self::STATUS_NOT_ATTENDED => 'عدم حضور',
            self::STATUS_DISAPPROVED  =>  'رد شده',
            self::STATUS_MONITORING   =>  'در انتظار تایید',
        };
    }
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'bg-warning',
            self::STATUS_SUCCESSFUL   => 'bg-success',
            self::STATUS_WAIT_PAYMENT => 'bg-info',
            self::STATUS_CANCEL, self::STATUS_DISAPPROVED => 'bg-danger',
            self::STATUS_ATTENDED     => 'bg-secondary',
            self::STATUS_NOT_ATTENDED => 'bg-primary',
            self::STATUS_MONITORING   => 'bg-warning',
            default => '',
        };
    }
    public function getButtonColor(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'btn-warning',
            self::STATUS_SUCCESSFUL   => 'btn-success',
            self::STATUS_WAIT_PAYMENT => 'btn-info',
            self::STATUS_CANCEL, self::STATUS_DISAPPROVED => 'btn-danger',
            self::STATUS_ATTENDED     => 'btn-secondary',
            self::STATUS_NOT_ATTENDED => 'btn-primary',
            self::STATUS_MONITORING   => 'btn-warning',
            default => '',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::STATUS_DISAPPROVED, self::STATUS_CANCEL => 'table-danger',
            self::STATUS_PENDING        => 'table-warning',
            self::STATUS_SUCCESSFUL     => 'table-success',
            self::STATUS_WAIT_PAYMENT   => 'table-primary',
            self::STATUS_MONITORING     => 'table-warning',
            default => '',
        };
    }

    public static function confirmed(): array
    {
        return [
            self::STATUS_PENDING->value,
            self::STATUS_SUCCESSFUL->value,
            self::STATUS_WAIT_PAYMENT->value,
            self::STATUS_ATTENDED->value,
            self::STATUS_NOT_ATTENDED->value,
            self::STATUS_DISAPPROVED->value,
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
