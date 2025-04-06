<?php

namespace Modules\Api\Enum;

enum ServiceQuestionEnum: int
{
    case Q1 = 1;
    case Q2 = 2;
    case Q3 = 3;

    public function getName(): string
    {
        return match ($this) {
            self::Q1      => 'هفته 4 تا 12 بارداری',
            self::Q2      => 'هفته ۱۳ تا ۳۵ بارداری',
            self::Q3      => 'هفته ۳۶ تا ۳۸ بارداری',
        };
    }
}
