<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Dashboard;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Dashboard\DashboardRequest;
use Modules\PractitionerApi\Services\PractitionerDashboardService;

#[Group('داشبورد', 'داده تجمیعی صفحه اصلی اپ پزشک در Tenant جاری', weight: 4)]
class DashboardController
{
    /**
     * داشبورد یک‌درخواستی پزشک
     *
     * اگر date ارسال نشود روز جاری در timezone تنظیمات Tenant استفاده می‌شود. کلاینت
     * شمارش معکوس را از server_time و starts_at/ends_at همگام می‌کند و می‌تواند بین
     * درخواست‌ها countdown را محلی کاهش دهد. مبلغ‌ها integer تومان و مدت‌ها مطابق
     * نام فیلد برحسب دقیقه یا ثانیه هستند. فقط نوبت‌های همین پزشک برگردانده می‌شوند.
     */
    #[ApiResponse(200, 'پروفایل خلاصه، وضعیت رزرو/VoIP، نوبت‌ها، آمار، هشدار و مالی روز. connected تا زمان اتصال زیرساخت ثبت زنده SIP مقدار null دارد و نباید false تفسیر شود.', type: 'array{data: array{server_time: string, timezone: string, date: string, date_jalali: string, date_label: string, practitioner: array{id: int, display_name: string, availability: string, active: bool, app_access: bool}, booking: array{enabled: bool}, voip: array{configured: bool, connected: bool|null, status: string, extension: string|null, server_host: string|null, server_port: int, transport: string, missing_fields: list<string>}, next_appointment: array<string, mixed>|null, upcoming_today: list<array<string, mixed>>, today_stats: array{total: int, completed: int, scheduled: int, failed: int, booked_minutes: int, calls: int, answered_calls: int, talk_seconds: int}, important_messages: list<array{type: string, appointment_id: int, message: string}>, financial_summary: array{settled_paid_amount: int, pending_paid_amount: int, practitioner_earned_amount: int, refundable_amount: int, refunded_amount: int, currency: string}, empty_state: array{show: bool, message: string|null}, week_strip: list<array<string, mixed>>}}', examples: [[
        'data' => [
            'server_time' => '2026-09-13T10:00:00+03:30', 'timezone' => 'Asia/Tehran',
            'date' => '2026-09-13', 'date_jalali' => '1405-06-22', 'date_label' => 'امروز — 22 شهریور 1405',
            'practitioner' => ['id' => 1, 'display_name' => 'دکتر نمونه', 'availability' => 'ready', 'active' => true, 'app_access' => true],
            'booking' => ['enabled' => true],
            'voip' => ['configured' => true, 'connected' => null, 'status' => 'configuration_ready', 'extension' => '102', 'server_host' => 'voip.example.test', 'server_port' => 5061, 'transport' => 'tls', 'missing_fields' => []],
            'next_appointment' => null, 'upcoming_today' => [],
            'today_stats' => ['total' => 0, 'completed' => 0, 'scheduled' => 0, 'failed' => 0, 'booked_minutes' => 0, 'calls' => 0, 'answered_calls' => 0, 'talk_seconds' => 0],
            'important_messages' => [],
            'financial_summary' => ['settled_paid_amount' => 0, 'pending_paid_amount' => 0, 'practitioner_earned_amount' => 0, 'refundable_amount' => 0, 'refunded_amount' => 0, 'currency' => 'TOMAN'],
            'empty_state' => ['show' => true, 'message' => 'برای این روز نوبتی ثبت نشده است؛ نوبت‌دهی فعال است.'],
            'week_strip' => [],
        ],
    ]])]
    #[ApiResponse(401, 'Bearer Token نامعتبر، منقضی یا ارسال‌نشده است.', type: 'array{message: string}', examples: [['message' => 'Unauthenticated.']])]
    #[ApiResponse(403, 'پزشک، دسترسی اپ یا ability توکن معتبر نیست.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [['message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.', 'errors' => []]])]
    #[ApiResponse(422, 'date با فرمت YYYY-MM-DD معتبر نیست.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'تاریخ باید با فرمت YYYY-MM-DD وارد شود.',
        'errors' => ['date' => ['تاریخ باید با فرمت YYYY-MM-DD وارد شود.']],
    ]])]
    public function __invoke(DashboardRequest $request, PractitionerDashboardService $dashboard): JsonResponse
    {
        return response()->json([
            'data' => $dashboard->get(
                $request->attributes->get('practitioner'),
                $request->validated('date'),
            ),
        ]);
    }
}
