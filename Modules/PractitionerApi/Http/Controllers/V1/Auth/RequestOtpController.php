<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Auth;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Header;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Auth\RequestOtpRequest;
use Modules\PractitionerApi\Services\PractitionerOtpService;
use Symfony\Component\HttpFoundation\Response;

#[Group('احراز هویت', 'ورود پزشکان و مشاوران با شماره موبایل و OTP', weight: 1)]
class RequestOtpController
{
    /**
     * ارسال کد ورود پزشک
     *
     * کد چهاررقمی ورود را با قالب پیامک login سامانه برای پزشک یا مشاور فعال همین
     * Tenant ارسال می‌کند. کد 120 ثانیه اعتبار دارد و ارسال مجدد پس از 60 ثانیه
     * مجاز است. حساب ناموجود، غیرفعال یا فاقد app_access پاسخ 403 دریافت می‌کند؛
     * فقط ورود تستی فعال، شرط app_access را برای پزشک/مشاور فعال نادیده می‌گیرد.
     */
    #[ApiResponse(202, 'در حالت عادی کد پیامک می‌شود. فقط هنگام فعال‌بودن ورود تستی، test_mode و test_code نیز برگردانده می‌شوند و پیامکی ارسال نمی‌شود.', type: 'array{message: string, data: array{expires_in: int, resend_after: int, test_mode?: bool, test_code?: string}}', examples: [[
        'message' => 'کد ورود ارسال شد.', 'data' => ['expires_in' => 120, 'resend_after' => 60],
    ], [
        'message' => 'ورود تستی فعال است.', 'data' => ['expires_in' => 120, 'resend_after' => 60, 'test_mode' => true, 'test_code' => '1234'],
    ]])]
    #[ApiResponse(403, 'پزشک یا مشاور فعال نیست یا دسترسی اپلیکیشن ندارد.', type: 'array{message: string}', examples: [[
        'message' => 'حساب فعال پزشک یا مشاور برای این شماره یافت نشد.',
    ]])]
    #[ApiResponse(422, 'شماره موبایل وجود ندارد یا با الگوی 09xxxxxxxxx منطبق نیست.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'شماره موبایل باید با فرمت 09123456789 وارد شود.', 'errors' => ['mobile' => ['شماره موبایل باید با فرمت 09123456789 وارد شود.']],
    ]])]
    #[ApiResponse(423, 'شماره به‌دلیل پنج تلاش اشتباه به‌مدت 15 دقیقه قفل است.', type: 'array{message: string, errors: array<string, list<string>>, retry_after: int}', examples: [[
        'message' => 'ورود موقتاً به‌دلیل تلاش‌های ناموفق قفل شده است.', 'errors' => ['code' => ['پس از پایان زمان قفل دوباره تلاش کنید.']], 'retry_after' => 840,
    ]])]
    #[ApiResponse(429, 'ارسال مجدد زودهنگام یا عبور از محدودیت IP/شماره. هدر Retry-After زمان انتظار را مشخص می‌کند.', type: 'array{message: string, errors: array<string, list<string>>, retry_after?: int}', examples: [[
        'message' => 'برای ارسال مجدد کد کمی صبر کنید.', 'errors' => [], 'retry_after' => 45,
    ]])]
    #[Header('Retry-After', 'تعداد ثانیه تا امکان تلاش بعدی.', type: 'int', example: 840, status: 423)]
    #[Header('Retry-After', 'تعداد ثانیه تا امکان درخواست بعدی.', type: 'int', example: 45, status: 429)]
    public function __invoke(RequestOtpRequest $request, PractitionerOtpService $otp): JsonResponse
    {
        $result = $otp->request(
            $request->validated('mobile'),
            $request->validated('device_identifier'),
            $request->ip(),
        );

        return response()->json([
            'message' => $result['test_mode'] ? 'ورود تستی فعال است.' : 'کد ورود ارسال شد.',
            'data' => [
                'expires_in' => (int) config('practitionerapi.otp.ttl_seconds', 120),
                'resend_after' => (int) config('practitionerapi.otp.resend_after_seconds', 60),
            ] + ($result['test_mode'] ? [
                'test_mode' => true,
                'test_code' => $result['test_code'],
            ] : []),
        ], Response::HTTP_ACCEPTED);
    }
}
