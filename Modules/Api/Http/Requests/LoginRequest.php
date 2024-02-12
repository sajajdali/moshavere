<?php

namespace Modules\Api\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * User should send mobile or email.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'mobile' => 'required_without:email',
            'email' => 'required_without:mobile|email',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required_without' => 'شماره موبایل یا ایمیل را وارد کنید',
            'email.required_without' => 'شماره موبایل یا ایمیل را وارد کنید',
        ];
    }

    /**
     * Only guests to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }
}
