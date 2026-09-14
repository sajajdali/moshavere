<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class CompleteAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['completion_confirmed' => ['required', 'accepted']]; }
}
