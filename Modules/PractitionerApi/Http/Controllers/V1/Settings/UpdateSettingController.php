<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Settings;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Settings\UpdateSettingRequest;
use Modules\PractitionerApi\Services\PractitionerSettingsService;

#[Group('تنظیمات اپ', 'تنظیمات شخصی هر پزشک در اپلیکیشن', weight: 3)]
class UpdateSettingController
{
    /**
     * تغییر یک تنظیم اپ
     *
     * کلیدهای مجاز: `push_appointments`، `push_calls`، `ring_sound` و
     * `auto_note_save`. ارسال کلید دیگر با 422 رد می‌شود و فیلدهای اضافی ذخیره نمی‌شوند.
     */
    #[ApiResponse(200, 'مقدار جدید ذخیره شد.', type: 'array{data: array{key: string, label: string, value: bool}}', examples: [[
        'data' => ['key' => 'ring_sound', 'label' => 'صدای زنگ', 'value' => false],
    ]])]
    #[ApiResponse(401, 'Bearer Token نامعتبر یا ارسال‌نشده است.', type: 'array{message: string}', examples: [['message' => 'Unauthenticated.']])]
    #[ApiResponse(403, 'دسترسی فعال پزشک وجود ندارد.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [['message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.', 'errors' => []]])]
    #[ApiResponse(422, 'key خارج از enum یا value غیربولی است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'کلید تنظیم انتخاب‌شده قابل تغییر نیست.', 'errors' => ['key' => ['کلید تنظیم انتخاب‌شده قابل تغییر نیست.']],
    ]])]
    public function __invoke(UpdateSettingRequest $request, PractitionerSettingsService $settings): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'data' => $settings->update(
                $request->attributes->get('practitioner'),
                $data['key'],
                $data['value'],
            ),
        ]);
    }
}
