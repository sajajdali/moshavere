<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** کلید تنظیم قابل تغییر */
            'key' => ['required', 'string', Rule::in(array_keys(config('practitionerapi.settings', [])))],
            /** مقدار روشن یا خاموش تنظیم */
            'value' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'کلید تنظیم الزامی است.',
            'key.in' => 'کلید تنظیم انتخاب‌شده قابل تغییر نیست.',
            'value.required' => 'مقدار تنظیم الزامی است.',
            'value.boolean' => 'مقدار تنظیم باید true یا false باشد.',
        ];
    }
}
