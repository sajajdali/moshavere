<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['mobile' => $this->normalizeMobile((string) $this->input('mobile'))]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            /** شماره موبایل پزشک با فرمت 09123456789 */
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            /** شناسه پایدار نصب اپلیکیشن */
            'device_identifier' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'شماره موبایل باید با فرمت 09123456789 وارد شود.',
            'device_identifier.string' => 'شناسه دستگاه باید رشته باشد.',
            'device_identifier.max' => 'شناسه دستگاه نباید بیشتر از 191 نویسه باشد.',
        ];
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', convert2english(trim($mobile))) ?? '';

        if (str_starts_with($mobile, '0098')) {
            $mobile = '0'.substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98')) {
            $mobile = '0'.substr($mobile, 2);
        }

        return $mobile;
    }
}
