<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** وضعیت پاسخ‌گویی: ready آماده، busy مشغول، offline آفلاین */
            'availability' => ['required', Rule::in(['ready', 'busy', 'offline'])],
        ];
    }

    public function messages(): array
    {
        return [
            'availability.required' => 'وضعیت پاسخ‌گویی الزامی است.',
            'availability.in' => 'وضعیت پاسخ‌گویی فقط می‌تواند ready، busy یا offline باشد.',
        ];
    }
}
