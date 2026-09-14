<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Profile;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerProfileService;

#[Group('پروفایل', 'اطلاعات پزشک احراز هویت‌شده در Tenant جاری', weight: 2)]
class SectionsController
{
    /**
     * برنامه کاری و بخش‌های پزشک
     *
     * اطلاعات فقط خواندنی است و از تنظیمات نوبت‌دهی Tenant استخراج می‌شود. weekday
     * از صفر شنبه تا شش جمعه است. ساعت‌ها `HH:mm` و مبلغ‌ها integer تومان هستند.
     */
    #[ApiResponse(200, 'برنامه پیش‌فرض و بخش‌ها.', type: 'array{data: array{editable: bool, patient_hourly_rate: int, practitioner_hourly_rate: int, default_hours: list<array{weekday: int, weekday_label: string, from: string|null, to: string|null, closed: bool}>, sections: list<array{key: string, type: string, name: string, active: bool, patient_hourly_rate: int, practitioner_hourly_rate: int, active_days: int, hours: list<array{weekday: int, weekday_label: string, from: string|null, to: string|null, closed: bool}>}>}}', examples: [[
        'data' => ['editable' => false, 'patient_hourly_rate' => 800000, 'practitioner_hourly_rate' => 600000, 'default_hours' => [['weekday' => 0, 'weekday_label' => 'شنبه', 'from' => '09:00', 'to' => '17:00', 'closed' => false]], 'sections' => []],
    ]])]
    #[ApiResponse(401, 'Bearer Token نامعتبر یا ارسال‌نشده است.', type: 'array{message: string}', examples: [['message' => 'Unauthenticated.']])]
    #[ApiResponse(403, 'دسترسی فعال پزشک وجود ندارد.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [['message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.', 'errors' => []]])]
    public function __invoke(Request $request, PractitionerProfileService $profiles): JsonResponse
    {
        return response()->json(['data' => $profiles->sections($request->attributes->get('practitioner'))]);
    }
}
