<?php

namespace Modules\Transaction\Enum;

use App\interface\EnumHasNameInterface;
use App\trait\EnumFunctionTrait;

enum TransactionPaymentForEnum : int implements EnumHasNameInterface
{
    use EnumFunctionTrait ;

    case IN_PERSON = 10;
    case ONLINE = 20;

    case BOTH = 30;

    public function getName(): string
    {
        return match ($this) {
            self::IN_PERSON => 'خحضوری',
            self::ONLINE => 'آنلاین',
            self::BOTH => 'هردو',
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
