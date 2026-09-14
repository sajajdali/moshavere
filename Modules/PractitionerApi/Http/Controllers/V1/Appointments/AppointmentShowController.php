<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Appointments;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerAppointmentService;

#[Group('نوبت‌ها', 'فهرست و جزئیات نوبت‌های متعلق به پزشک جاری', weight: 5)]
class AppointmentShowController
{
    /**
     * جزئیات نوبت پزشک
     *
     * نوبت متعلق به پزشک دیگر عمداً مانند نوبت ناموجود پاسخ 404 دارد. actions مرجع
     * قطعی نمایش و فعال‌بودن دکمه‌ها است؛ کلاینت قوانین تجاری را تکرار نمی‌کند.
     * این endpoint عملیات را اجرا نمی‌کند و فقط مجازبودن فعلی را گزارش می‌دهد.
     */
    #[ApiResponse(200, 'جزئیات زمان، بیمار، بخش، تماس، مالی، گزارش و actions. allowed تنها مرجع فعال‌بودن عملیات و reason_code کد پایدار تصمیم سرور است.', type: 'array{data: array{id: int, file_no: string|null, server_time: string, timezone: string, starts_at: string|null, ends_at: string|null, duration_minutes: int, phase: string, status: string, status_reason: string|null, countdown: array{starts_in_seconds: int|null, ends_in_seconds: int|null}, patient: array{id: int|null, full_name: string|null, mobile: string|null}, section: array{key: string, name: string}, complaint_summary: string|null, calls_summary: array{total: int, answered: int, unanswered: int, talk_seconds: int}, call_stats: array{total: int, answered: int, unanswered: int, early: int, talk_seconds: int}, reports_count: int, settlement: array{status: string, reserved_minutes: int, talk_seconds: int, total_paid_amount: int, suggested_refund_amount: int, refunded_amount: int, practitioner_earned_amount: int, currency: string}, actions: array{complete: array{allowed: bool, reason_code: string|null, reason: string|null, requires_report: bool, reports_count: int}, no_show: array{allowed: bool, reason_code: string|null, reason: string|null}, auto_call: array{allowed: bool, reason_code: string|null, reason: string|null, allowed_after: string|null}, settlement: array{allowed: bool, reason_code: string|null, reason: string|null, status: string}}}}')]
    #[ApiResponse(401, 'توکن معتبر نیست.', type: 'array{message: string}')]
    #[ApiResponse(403, 'دسترسی پزشک فعال نیست.', type: 'array{message: string, errors: array<string, list<string>>}')]
    #[ApiResponse(404, 'نوبت وجود ندارد، لغو شده یا متعلق به پزشک دیگری است.', type: 'array{message: string}', examples: [['message' => 'نوبت برای این پزشک یافت نشد.']])]
    public function __invoke(Request $request, int $appointment, PractitionerAppointmentService $appointments): JsonResponse
    {
        return response()->json(['data' => $appointments->detail($request->attributes->get('practitioner'), $appointment)]);
    }
}
