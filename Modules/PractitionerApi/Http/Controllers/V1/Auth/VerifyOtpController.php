<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Auth;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Header;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Auth\VerifyOtpRequest;
use Modules\PractitionerApi\Services\PractitionerOtpService;

#[Group('احراز هویت', 'ورود پزشکان و مشاوران با شماره موبایل و OTP', weight: 1)]
class VerifyOtpController
{
    /**
     * تأیید کد و ورود به اپلیکیشن
     *
     * پس از تأیید OTP، Bearer Token نودروزه Sanctum مخصوص نصب اپ صادر می‌کند. پنج
     * ورود اشتباه، شماره را 15 دقیقه قفل می‌کند. ورود دوباره با همان
     * device_identifier توکن قبلی همان نصب را باطل می‌کند، اما سایر دستگاه‌ها فعال
     * می‌مانند. زمان expires_at به ISO-8601 همراه offset برگردانده می‌شود.
     */
    #[ApiResponse(200, 'ورود موفق، صدور Bearer token و اطلاعات کامل ثبت SIP. password محرمانه است و نباید log یا خارج از حافظه امن نگهداری شود. test_mode و test_code فقط در حالت ورود تستی فعال برمی‌گردند.', type: 'array{message: string, data: array{token_type: string, access_token: string, expires_at: string, practitioner: array{id: int, user_id: int, display_name: string, kind: string, specialty: string|null, availability: string, softphone: array{configured: bool, server_address: string|null, server_host: string|null, server_port: int, transport: string, extension: string|null, username: string|null, password: string|null, missing_fields: list<string>}}, test_mode?: bool, test_code?: string}}', examples: [[
        'message' => 'ورود با موفقیت انجام شد.',
        'data' => [
            'token_type' => 'Bearer',
            'access_token' => '1|example-token',
            'expires_at' => '2026-12-12T12:00:00+03:30',
            'practitioner' => [
                'id' => 12,
                'user_id' => 48,
                'display_name' => 'دکتر نمونه',
                'kind' => 'doctor',
                'specialty' => 'روان‌شناسی',
                'availability' => 'offline',
                'softphone' => ['configured' => true, 'server_address' => 'https://voip.example.test:2214', 'server_host' => 'voip.example.test', 'server_port' => 5061, 'transport' => 'tls', 'extension' => '102', 'username' => 'advisor102', 'password' => 'individual-sip-password', 'missing_fields' => []],
            ],
        ],
    ], [
        'message' => 'ورود با موفقیت انجام شد.',
        'data' => [
            'token_type' => 'Bearer', 'access_token' => '1|example-token',
            'expires_at' => '2026-12-12T12:00:00+03:30',
            'practitioner' => ['id' => 12, 'user_id' => 48, 'display_name' => 'دکتر نمونه', 'kind' => 'doctor', 'specialty' => null, 'availability' => 'offline', 'softphone' => ['configured' => true, 'server_address' => 'https://voip.example.test:2214', 'server_host' => 'voip.example.test', 'server_port' => 5061, 'transport' => 'tls', 'extension' => '102', 'username' => 'advisor102', 'password' => 'individual-sip-password', 'missing_fields' => []]],
            'test_mode' => true, 'test_code' => '1234',
        ],
    ]])]
    #[ApiResponse(403, 'حساب پزشک هنگام تأیید کد غیرفعال یا دسترسی اپ آن لغو شده است.', type: 'array{message: string}', examples: [[
        'message' => 'حساب فعال پزشک یا مشاور برای این شماره یافت نشد.',
    ]])]
    #[ApiResponse(422, 'ورودی نامعتبر، OTP اشتباه، منقضی یا قبلاً مصرف‌شده است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'کد ورود صحیح نیست.',
        'errors' => ['code' => ['کد ورود صحیح نیست.']],
    ]])]
    #[ApiResponse(423, 'پس از پنج تلاش اشتباه، بررسی OTP به‌مدت 15 دقیقه قفل می‌شود.', type: 'array{message: string, errors: array<string, list<string>>, retry_after: int}', examples: [[
        'message' => 'ورود موقتاً به‌دلیل تلاش‌های ناموفق قفل شده است.',
        'errors' => ['code' => ['پس از پایان زمان قفل دوباره تلاش کنید.']],
        'retry_after' => 900,
    ]])]
    #[ApiResponse(429, 'عبور از محدودیت بررسی OTP برای IP یا شماره. هدر Retry-After ارسال می‌شود.', type: 'array{message: string}', examples: [[
        'message' => 'Too Many Attempts.',
    ]])]
    #[Header('Retry-After', 'تعداد ثانیه تا پایان قفل ورود.', type: 'int', example: 900, status: 423)]
    #[Header('Retry-After', 'تعداد ثانیه تا امکان تلاش بعدی.', type: 'int', example: 60, status: 429)]
    public function __invoke(VerifyOtpRequest $request, PractitionerOtpService $otp): JsonResponse
    {
        $result = $otp->verify(
            $request->validated('mobile'),
            $request->validated('code'),
            $request->safe()->except(['mobile', 'code']),
        );
        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $result['token'],
                'expires_at' => $result['expires_at'],
                'practitioner' => $result['practitioner'],
            ] + (($result['test_mode'] ?? false) ? [
                'test_mode' => true,
                'test_code' => $result['test_code'],
            ] : []),
        ]);
    }
}
