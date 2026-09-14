<?php
namespace Modules\PractitionerApi\Http\Requests\V1\Voip;
use Illuminate\Foundation\Http\FormRequest;
class UpdateVoipConfigRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'extension' => ['required', 'regex:/^[0-9]{1,20}$/'],
        'username' => ['required', 'string', 'max:255'],
        'password' => ['nullable', 'string', 'max:1024'],
    ]; }
}
