<?php

namespace Modules\Api\app\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimestampValidationRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!strtotime($value)) {
            $fail('مقدار :attribute باید از نوع timestamp باشد');
        }
    }
}
