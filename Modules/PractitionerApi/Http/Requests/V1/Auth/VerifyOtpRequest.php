<?php

namespace Modules\PractitionerApi\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $mobile = preg_replace('/\D+/', '', convert2english(trim((string) $this->input('mobile')))) ?? '';
        if (str_starts_with($mobile, '0098')) {
            $mobile = '0'.substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98')) {
            $mobile = '0'.substr($mobile, 2);
        }

        $this->merge([
            'mobile' => $mobile,
            'code' => convert2english((string) $this->input('code')),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            /** شماره موبایل پزشک با فرمت 09123456789 */
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            /** کد چهاررقمی ارسال‌شده با پیامک */
            'code' => ['required', 'digits:4'],
            /** نام قابل تشخیص دستگاه، مانند Sajad iPhone */
            'device_name' => ['required', 'string', 'max:100'],
            /** سیستم‌عامل اپلیکیشن */
            'device_os' => ['required', Rule::in(['android', 'ios'])],
            /** شناسه پایدار نصب اپلیکیشن */
            'device_identifier' => ['required', 'string', 'max:191'],
            /** توکن Firebase برای اعلان‌ها */
            'fcm_token' => ['nullable', 'string', 'max:4096'],
            'device_version' => ['nullable', 'string', 'max:100'],
            'device_info' => ['nullable', 'array'],
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
            'code.required' => 'کد ورود الزامی است.',
            'code.digits' => 'کد ورود باید دقیقاً چهار رقم باشد.',
            'device_name.required' => 'نام دستگاه الزامی است.',
            'device_name.max' => 'نام دستگاه نباید بیشتر از 100 نویسه باشد.',
            'device_os.required' => 'سیستم‌عامل دستگاه الزامی است.',
            'device_os.in' => 'سیستم‌عامل فقط می‌تواند android یا ios باشد.',
            'device_identifier.required' => 'شناسه پایدار نصب اپلیکیشن الزامی است.',
            'device_identifier.max' => 'شناسه دستگاه نباید بیشتر از 191 نویسه باشد.',
            'fcm_token.max' => 'توکن اعلان نباید بیشتر از 4096 نویسه باشد.',
            'device_version.max' => 'نسخه اپ نباید بیشتر از 100 نویسه باشد.',
            'device_info.array' => 'اطلاعات دستگاه باید یک object معتبر باشد.',
        ];
    }
}
