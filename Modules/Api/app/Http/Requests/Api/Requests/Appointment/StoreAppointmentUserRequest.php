<?php

namespace Modules\Api\app\Http\Requests\Api\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Api\app\Rules\TimestampValidationRule;

class StoreAppointmentUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'timestamp' => ['required_if:kind,1']
        ];
    }

    public function messages(): array
    {
        return [
            'timestamp.required_if' => 'وارد کردن زمان نوبت اجباری است',
        ];
    }    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
