<?php

namespace Modules\Front\Enum;

enum CommentStatusEnum: int
{
    case PENDING = 0;
    case ACCEPTED = 1;
    case REJECTED = 2;

    public function getName(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار تایید',
            self::ACCEPTED => 'تایید شده',
            self::REJECTED => 'رد شده',
            default => '',
        };
    }
}
