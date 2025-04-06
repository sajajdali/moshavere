<?php

namespace Modules\Api\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => 'required_without:email',
            'email' => 'required_without:mobile|email',
            'code' => 'required|digits:4',
            'device_os' => 'required|in:android,ios,web',
            'statement' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required_without' => 'شماره موبایل یا ایمیل را وارد کنید',
            'code.required' => 'کد را وارد کنید',
            'code.digits' => 'کد باید ۴ رقمی باشد',
            'device_os.required' => 'سیستم عامل را وارد کنید',
            'device_os.in' => 'سیستم عامل معتبر نیست',
            'email.required_without' => 'شماره موبایل یا ایمیل را وارد کنید',
            'statement.statement' => 'اطلاعات پرونده شما ناقص است',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }
}
