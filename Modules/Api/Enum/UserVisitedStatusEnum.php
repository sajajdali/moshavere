<?php

namespace Modules\Api\Enum;

enum UserVisitedStatusEnum: int
{
    case HAS_BEEN_VISITED = 10;
    case DOSET_VISITED = 20;

    public function getName(): string
    {
        return match ($this) {
            self::HAS_BEEN_VISITED      => 'قبلا ویزیت شده است',
            self::DOSET_VISITED   => 'قبلا ویزیت نشده است',
        };
    }
}
