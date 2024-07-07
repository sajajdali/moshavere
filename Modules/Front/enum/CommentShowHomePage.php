<?php

namespace Modules\Front\Enum;

enum CommentShowHomePage: int
{
    case DONT_SHOW = 0;
    case SHOW = 1;

    public function getName(): string
    {
        return match ($this) {
            self::DONT_SHOW => 'عدم نمایش',
            self::SHOW => 'رد شده',
            default => '',
        };
    }
    public function getButtonColor()
    {
        return match ($this) {
            self::DONT_SHOW => 'btn-danger',
            self::SHOW => 'btn-success',
            default => '',
        };
    }
    public function boolStatus()
    {
        return match ($this) {
            self::DONT_SHOW => false,
            self::SHOW => true,
            default => '',
        };
    }
}
