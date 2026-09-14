<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Settings;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerSettingsService;

#[Group('تنظیمات اپ', 'تنظیمات شخصی هر پزشک در اپلیکیشن', weight: 3)]
class SettingsController
{
    /**
     * دریافت تنظیمات اپ پزشک
     *
     * کلیدها توسط سرور تعریف می‌شوند. کلاینت باید label را نمایش دهد و مقدار boolean
     * را برای ویرایش همان key ارسال کند.
     */
    #[ApiResponse(200, 'فهرست تنظیمات قابل تغییر.', type: 'array{data: array{toggles: list<array{key: string, label: string, value: bool}>}}', examples: [[
        'data' => ['toggles' => [['key' => 'ring_sound', 'label' => 'صدای زنگ', 'value' => true]]],
    ]])]
    #[ApiResponse(401, 'Bearer Token نامعتبر یا ارسال‌نشده است.', type: 'array{message: string}', examples: [['message' => 'Unauthenticated.']])]
    #[ApiResponse(403, 'دسترسی فعال پزشک وجود ندارد.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [['message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.', 'errors' => []]])]
    public function __invoke(Request $request, PractitionerSettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => ['toggles' => $settings->all($request->attributes->get('practitioner'))],
        ]);
    }
}
