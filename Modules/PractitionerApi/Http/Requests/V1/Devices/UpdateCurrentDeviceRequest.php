<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Devices;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrentDeviceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'device_identifier' => ['required', 'string', 'max:255'],
            'fcm_token' => ['present', 'nullable', 'string', 'max:4096'],
            'device_version' => ['nullable', 'string', 'max:100'],
        ];
    }
}
