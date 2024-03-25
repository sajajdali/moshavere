<?php
namespace Modules\AppointmentUser\Enum;

use App\interface\EnumHasApiResultInterface;
use PhpParser\Node\Expr\Array_;

enum AppointmentUserStatusEnum : int implements EnumHasApiResultInterface {
    case STATUS_PENDING = 0;
    case STATUS_SUCCESSFUL = 1;
    case STATUS_WAIT_PAYMENT = 2;
    case STATUS_CANCEL = 3;

    public function getName(): string
    {
        return match($this) {
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_SUCCESSFUL => 'تایید شده',
            self::STATUS_WAIT_PAYMENT => 'منتظر پرداخت',
            self::STATUS_CANCEL => 'کنسل شده',
        };
    }

    public function apiResult() : array
    {
        return [
            'name' => $this->value,
            'body' => $this->getName()
        ];
    }


}
