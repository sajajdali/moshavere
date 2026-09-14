<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $query = trim(convert2english((string) $this->input('q')));
        $this->merge(['q' => $query === '' ? null : $query]);
    }

    public function rules(): array
    {
        return [
            /** بازه فهرست؛ date در صورت ارسال بر scope اولویت دارد */
            'scope' => ['nullable', Rule::in(['today', 'tomorrow', 'past', 'all'])],
            /** روز میلادی با فرمت YYYY-MM-DD */
            'date' => ['nullable', 'date_format:Y-m-d'],
            /** جست‌وجوی نام یا موبایل بیمار */
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
