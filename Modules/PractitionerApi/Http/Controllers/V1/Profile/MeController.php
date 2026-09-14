<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Profile;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerProfileService;

#[Group('پروفایل', 'اطلاعات پزشک احراز هویت‌شده در Tenant جاری', weight: 2)]
class MeController
{
    /**
     * پروفایل پزشک یا مشاور واردشده
     *
     * اطلاعات حساب کاربری و پروفایل فعال اپلیکیشن را برمی‌گرداند.
     */
    #[ApiResponse(200, 'پروفایل فعال پزشک و اطلاعات کامل ثبت SIP در Tenant جاری. password محرمانه است و کلاینت نباید آن را log یا ذخیره دائمی کند. مبلغ‌ها integer تومان و مدت پیش‌فرض برحسب دقیقه است.', type: 'array{data: array{id: int, user_id: int, display_name: string, initials: string, kind: string, specialty: string|null, specialties: list<array{id: int, title: string}>, activity_centers: list<array{id: int, title: string}>, avatar_url: string|null, license_number: string|null, availability: string, booking_enabled: bool, extension: string|null, softphone: array{configured: bool, server_address: string|null, server_host: string|null, server_port: int, transport: string, extension: string|null, username: string|null, password: string|null, missing_fields: list<string>}, default_duration_minutes: int, patient_hourly_rate: int, practitioner_hourly_rate: int, server_time: string}}', examples: [[
        'data' => [
            'id' => 12,
            'user_id' => 48,
            'display_name' => 'دکتر نمونه',
            'initials' => 'ن',
            'kind' => 'doctor',
            'specialty' => 'روان‌شناسی',
            'specialties' => [['id' => 3, 'title' => 'روان‌شناسی بالینی']],
            'activity_centers' => [['id' => 2, 'title' => 'کلینیک مرکزی']],
            'avatar_url' => null,
            'license_number' => 'PS-24518',
            'availability' => 'offline',
            'booking_enabled' => true,
            'extension' => '102',
            'softphone' => [
                'configured' => true,
                'server_address' => 'https://voip.example.test:2214',
                'server_host' => 'voip.example.test',
                'server_port' => 5061,
                'transport' => 'tls',
                'extension' => '102',
                'username' => 'advisor102',
                'password' => 'individual-sip-password',
                'missing_fields' => [],
            ],
            'default_duration_minutes' => 20,
            'patient_hourly_rate' => 800000,
            'practitioner_hourly_rate' => 600000,
            'server_time' => '2026-09-13T20:00:00+03:30',
        ],
    ]])]
    #[ApiResponse(401, 'Bearer Token ارسال نشده، نامعتبر یا منقضی است.', type: 'array{message: string}', examples: [[
        'message' => 'Unauthenticated.',
    ]])]
    #[ApiResponse(403, 'توکن ability لازم را ندارد یا پزشک/app_access غیرفعال شده است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.',
        'errors' => [],
    ]])]
    public function __invoke(Request $request, PractitionerProfileService $profiles): JsonResponse
    {
        return response()->json(['data' => $profiles->profile($request->attributes->get('practitioner'))]);
    }
}
