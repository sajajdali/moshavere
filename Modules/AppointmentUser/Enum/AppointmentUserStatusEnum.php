<?php

namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;

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
    case STATUS_ONILNE_CLOSED = 8;

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
            self::STATUS_ONILNE_CLOSED   =>  'نوبت آنلاین تکمیل شده',
        };
    }
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::STATUS_PENDING      => 'bg-warning',
            self::STATUS_SUCCESSFUL   => 'bg-success',
            self::STATUS_WAIT_PAYMENT => 'bg-info',
            self::STATUS_CANCEL, self::STATUS_DISAPPROVED, self::STATUS_ONILNE_CLOSED => 'bg-danger',
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
            self::STATUS_CANCEL, self::STATUS_DISAPPROVED, self::STATUS_ONILNE_CLOSED => 'btn-danger',
            self::STATUS_ATTENDED     => 'btn-secondary',
            self::STATUS_NOT_ATTENDED => 'btn-primary',
            self::STATUS_MONITORING   => 'btn-warning',
            default => '',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::STATUS_DISAPPROVED, self::STATUS_CANCEL, self::STATUS_ONILNE_CLOSED => 'table-danger',
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
            self::STATUS_ONILNE_CLOSED->value,
        ];
    }

    public function convertToAppointmentOnlineStauts() {
        return match ($this) {
            self::STATUS_PENDING      => AppointmentOnlineStatusEnum::PENDING,
            self::STATUS_SUCCESSFUL   => AppointmentOnlineStatusEnum::ACCEPTED,
            self::STATUS_WAIT_PAYMENT => AppointmentOnlineStatusEnum::PENDING,
            self::STATUS_CANCEL       => AppointmentOnlineStatusEnum::CANCEL,
            self::STATUS_ATTENDED     => AppointmentOnlineStatusEnum::ACCEPTED,
            self::STATUS_NOT_ATTENDED => AppointmentOnlineStatusEnum::ACCEPTED,
            self::STATUS_DISAPPROVED  =>  AppointmentOnlineStatusEnum::REJECT,
            self::STATUS_MONITORING   =>  AppointmentOnlineStatusEnum::PENDING,
            self::STATUS_ONILNE_CLOSED   => AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR,
            default => AppointmentOnlineStatusEnum::ACCEPTED
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
