<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyReportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'scope' => ['nullable', Rule::in(['today', 'date', 'last_7_days'])],
            'date' => ['nullable', 'required_if:scope,date', 'date_format:Y-m-d'],
        ];
    }
}
