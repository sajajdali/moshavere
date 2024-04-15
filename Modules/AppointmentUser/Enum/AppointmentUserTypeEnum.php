<?php

namespace Modules\AppointmentUser\Enum;
use App\interface\EnumHasApiResultInterface;
use App\interface\EnumHasNameInterface;

enum AppointmentUserTypeEnum: int implements EnumHasNameInterface , EnumHasApiResultInterface
{
    case MAIN__APPOINTMENT = 1;
    case BETWEEN_PATIENTS = 2;

    public function getName(): string
    {
        return match($this) {
            self::MAIN__APPOINTMENT => 'اصلی',
            self::BETWEEN_PATIENTS => 'بین مریض',
        };
    }

    public function getbage() {
        return match($this) {
            self::MAIN__APPOINTMENT => '',
            self::BETWEEN_PATIENTS => ' <span class="badge bg-primary rounded-pill">بین مریض</span>',
        };
    }
    public function getclass() {
        return match($this) {
            self::MAIN__APPOINTMENT => '',
            self::BETWEEN_PATIENTS => 'd-flex flex-column',
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
