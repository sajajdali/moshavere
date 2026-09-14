<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Settlements;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmSettlementRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'settlement_confirmed' => ['required', 'accepted'],
            'approved_unused_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
