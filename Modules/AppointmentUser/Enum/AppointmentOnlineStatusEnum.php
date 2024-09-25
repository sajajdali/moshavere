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

    public static function showInDashboardApi(): array
    {
        return [
            self::ACCEPTED->value,
            self::REPLY_BY_USER->value,
            self::ANSWER_BY_DOCTOR->value,
            self::REACTIVATED->value
        ];
    }
    public function canSendMessage(): bool
    {
        return match ($this) {
            self::ACCEPTED, self::REPLY_BY_USER, self::ANSWER_BY_DOCTOR, self::REACTIVATED => true,
            default => false
        };
    }

    public function canShowMessages(): bool
    {
        return true;
//        return match ($this) {
//            self::ACCEPTED, self::REPLY_BY_USER, self::ANSWER_BY_DOCTOR, self::COMPLETED_BY_DOCTOR, self::TIME_IS_OVER, self::REACTIVATED => true,
//            default => false
//        };
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
    public function getMessageDetailBadge()
    {

        return match ($this) {
            self::PENDING => '<span class="badge bg-info  rounded-pill text-white ms-1">در انتظار</span>',
            self::ACCEPTED => '<span class="badge bg-success rounded-pill ms-1">تایید شده</span>',
            self::REJECT => '<span class="badge bg-danger rounded-pill ms-1">رد شده</span>',
            self::CANCEL => '<span class="badge bg-danger rounded-pill ms-1">کنسل شده</span>',
            self::REPLY_BY_USER => '<span class="badge bg-primary rounded-pill ms-1">پاسخ کاربر</span>',
            self::ANSWER_BY_DOCTOR => '<span class="badge bg-success rounded-pill ms-1">پاسخ داده شده</span>',
            self::COMPLETED_BY_DOCTOR => '<span class="badge bg-warning rounded-pill ms-1">اتمام رسیده</span>',
            self::TIME_IS_OVER => '<span class="badge bg-danger rounded-pill ms-1">زمان ویزیت تمام شده</span>',
            self::REACTIVATED => '<span class="badge bg-success rounded-pill ms-1" >مجدد فعال شده</span>',
        };
    }
    public function isPendding()
    {
        return match ($this) {
            self::PENDING => true,
            default => false,
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
