<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Iranian national code: 10 digits whose last digit is the check digit.
 */
class NationalCodeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('کد ملی معتبر نیست.');
        }
    }

    public static function isValid(string $code): bool
    {
        // All-same-digit codes pass the checksum but are never issued.
        if (! preg_match('/^\d{10}$/', $code) || preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $check = (int) $code[9];

        return $remainder < 2 ? $check === $remainder : $check === 11 - $remainder;
    }
}
