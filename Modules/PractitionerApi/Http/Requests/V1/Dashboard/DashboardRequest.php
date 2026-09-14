<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** روز انتخاب‌شده با فرمت میلادی YYYY-MM-DD؛ پیش‌فرض امروز Tenant */
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format' => 'تاریخ باید با فرمت YYYY-MM-DD وارد شود.',
        ];
    }
}
