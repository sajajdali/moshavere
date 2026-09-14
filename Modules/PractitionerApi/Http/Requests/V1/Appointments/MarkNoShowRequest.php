<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class MarkNoShowRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['no_show_confirmed' => ['required', 'accepted']]; }
}
