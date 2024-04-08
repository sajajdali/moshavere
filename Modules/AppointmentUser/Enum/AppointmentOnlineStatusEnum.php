<?php

namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;
use PhpParser\Node\Expr\Array_;

enum AppointmentOnlineStatusEnum: int implements EnumHasApiResultInterface
{
    case PENDING = 0;
    case ACCEPTED = 1;
    case REJECT = 2;
    case CANCEL = 3;
    case REPLY_BY_USER = 4;
    case ANSWER_BY_DOCTOR = 5;
    case COMPLETED_BY_DOCTOR = 6;
    case TIME_IS_OVER = 7;
    case REACTIVATED = 8;

    public function getName(): string
    {
        return match ($this) {
            self::PENDING      => 'در انتظار',
            self::ACCEPTED   => 'تایید شده',
            self::REJECT => 'رد شده',
            self::CANCEL       => 'کنسل شده',
            self::REPLY_BY_USER     => 'پاسخ کاربر',
            self::ANSWER_BY_DOCTOR => 'جواب داده شده',
            self::COMPLETED_BY_DOCTOR => 'نوبت به اتمام رسیده',
            self::TIME_IS_OVER => 'زمان ویزیت تمام شده',
            self::REACTIVATED => 'مجدد فعال شده',
        };
    }
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::PENDING, self::REACTIVATED => 'bg-info',
            self::ACCEPTED   => 'bg-success',
            self::COMPLETED_BY_DOCTOR, self::TIME_IS_OVER => 'bg-warning',
            self::REJECT, self::CANCEL => 'bg-danger',
            self::REPLY_BY_USER     => 'bg-secondary',
            self::ANSWER_BY_DOCTOR => 'bg-primary',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING, self::REACTIVATED => 'table-info',
            self::ACCEPTED   => 'table-success',
            self::COMPLETED_BY_DOCTOR, self::TIME_IS_OVER => 'table-warning',
            self::REJECT, self::CANCEL => 'table-danger',
            self::REPLY_BY_USER     => 'table-secondary',
            self::ANSWER_BY_DOCTOR => 'table-primary',
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
