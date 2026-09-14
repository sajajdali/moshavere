<?php
namespace Modules\PractitionerApi\Http\Requests\V1\Calls;
use Illuminate\Foundation\Http\FormRequest;
class UpdateCallNoteRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['note' => ['nullable', 'string', 'max:3000']]; }
}
