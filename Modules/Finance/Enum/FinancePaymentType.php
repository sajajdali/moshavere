<?php

namespace Modules\Finance\Enum;

enum FinancePaymentType: string
{
    case PAYMENT = 'payment';
    case REFUND = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'دریافت',
            self::REFUND => 'بازگشت وجه',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PAYMENT => 'bg-success',
            self::REFUND => 'bg-danger',
        };
    }

    /** a refund reduces the money received from the patient */
    public function sign(): int
    {
        return $this === self::REFUND ? -1 : 1;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
