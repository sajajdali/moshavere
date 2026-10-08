<?php

namespace Modules\Finance\Enum;

/**
 * How a manual payment was made. The `sys_*` keys are only used in reports to name the
 * payments that were made through the system (transactions table).
 */
enum FinancePaymentMethod: string
{
    case POS = 'pos';
    case CARD_TO_CARD = 'card_to_card';
    case CASH = 'cash';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::POS => 'کارتخوان (پوز)',
            self::CARD_TO_CARD => 'کارت به کارت',
            self::CASH => 'نقدی',
            self::OTHER => 'سایر',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::POS => 'fa-credit-card',
            self::CARD_TO_CARD => 'fa-exchange',
            self::CASH => 'fa-money',
            self::OTHER => 'fa-ellipsis-h',
        };
    }

    /** the methods a manual payment can be registered with */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $m) => [$m->value => $m->label()])->all();
    }

    /** every method key that can appear in a report, manual and system */
    public static function reportLabels(): array
    {
        return self::options() + [
            'sys_online' => 'پرداخت آنلاین (سیستمی)',
            'sys_card_to_card' => 'کارت به کارت (سیستمی)',
            'sys_by_admin' => 'ثبت شده توسط ادمین (سیستمی)',
            'sys_wallet' => 'کیف پول (سیستمی)',
            'sys_other' => 'سایر (سیستمی)',
        ];
    }
}
