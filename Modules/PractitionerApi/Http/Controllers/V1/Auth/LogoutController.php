<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Auth;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Api\Entities\UserDevice;

#[Group('احراز هویت', 'ورود پزشکان و مشاوران با شماره موبایل و OTP', weight: 1)]
class LogoutController
{
    /**
     * خروج از دستگاه جاری
     *
     * فقط Bearer Token ارسال‌شده در همین درخواست و رکورد دستگاه وابسته به آن را باطل
     * می‌کند. نشست سایر دستگاه‌های پزشک فعال باقی می‌ماند. تکرار درخواست با توکن
     * باطل‌شده پاسخ 401 دارد.
     */
    #[ApiResponse(200, 'توکن دستگاه جاری با موفقیت باطل شد.', type: 'array{message: string}', examples: [[
        'message' => 'خروج با موفقیت انجام شد.',
    ]])]
    #[ApiResponse(401, 'Bearer Token ارسال نشده، نامعتبر یا منقضی است.', type: 'array{message: string}', examples: [[
        'message' => 'Unauthenticated.',
    ]])]
    #[ApiResponse(403, 'توکن ability اپ پزشک را ندارد یا دسترسی پزشک غیرفعال شده است.', type: 'array{message: string, errors: array<string, list<string>>}', examples: [[
        'message' => 'دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.',
        'errors' => [],
    ]])]
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        UserDevice::withTrashed()->where('access_token_id', $token->getKey())->forceDelete();
        $token->delete();

        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.',
        ]);
    }
}
