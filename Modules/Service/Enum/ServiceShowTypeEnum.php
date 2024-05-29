<?php

namespace Modules\Service\Enum;
use App\interface\EnumHasApiResultInterface;
use App\interface\EnumHasNameInterface;

enum ServiceShowTypeEnum: int implements EnumHasNameInterface , EnumHasApiResultInterface
{
    case SHOW = 1;
    case DONT_SHOW = 2;

    public function getName(): string
    {
        return match($this) {
            self::SHOW => 'نمایش در صفحه اصلی',
            self::DONT_SHOW => 'عدم نمایش',
        };
    }

    public function getBadge() {
        return match($this) {
            self::SHOW => ' <span class="badge bg-success">
            <i class="fa fa-check" aria-hidden="true"></i>
        </span>',
            self::DONT_SHOW => ' <span class="badge bg-danger">
            <i class="fa fa-times" aria-hidden="true"></i>
        </span>',
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
