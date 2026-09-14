<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Profile;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Profile\UpdateAvailabilityRequest;

#[Group('پروفایل', 'اطلاعات پزشک احراز هویت‌شده در Tenant جاری', weight: 2)]
class UpdateAvailabilityController
{
    /**
     * تغییر وضعیت پاسخ‌گویی پزشک
     *
     * `ready` یعنی آماده پاسخ‌گویی، `busy` یعنی مشغول و `offline` یعنی آفلاین.
     * این endpoint وضعیت رزرو نوبت را تغییر نمی‌دهد.
     */
    #[ApiResponse(200, 'وضعیت ذخیره شد.', type: 'array{data: array{availability: string}}', examples: [[
        'data' => ['availability' => 'busy'],
    ]])]
    #[ApiResponse(401, 'Bearer Token نامعتبر، منقضی یا ارسال‌نشده است.', type: 'array{message: string}', examples: [['message' => 'Unauthenticated.']])]
    #[ApiResponse(403, 'پزشک یا دسترسی اپ غیرفعال شده است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.', 'errors' => [],
    ]])]
    #[ApiResponse(422, 'availability ارسال نشده یا خارج از enum مجاز است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'وضعیت پاسخ‌گویی فقط می‌تواند ready، busy یا offline باشد.',
        'errors' => ['availability' => ['وضعیت پاسخ‌گویی فقط می‌تواند ready، busy یا offline باشد.']],
    ]])]
    public function __invoke(UpdateAvailabilityRequest $request): JsonResponse
    {
        $practitioner = $request->attributes->get('practitioner');
        $practitioner->update(['availability' => $request->validated('availability')]);

        return response()->json(['data' => ['availability' => $practitioner->availability]]);
    }
}
