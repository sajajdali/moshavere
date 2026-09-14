<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Calls;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Modules\PractitionerApi\Services\PractitionerCallService;
#[Group('تماس و VoIP', 'تماس‌های متعلق به پزشک جاری', weight: 6)] class AutoCallController {
    /** درخواست تماس خودکار؛ فقط پس از مهلت بیمار، با VoIP کامل و بدون درخواست معتبر تکراری. */
    #[ApiResponse(202, 'سرور VoIP درخواست را پذیرفت.', type: 'array{data: array{request_id:string, appointment_id:int, state:string, requested_at:string|null, extension:string}}')]
    #[ApiResponse(409, 'زودهنگام، تکراری یا تنظیمات ناقص است.', type: 'array{message:string}')]
    #[ApiResponse(503, 'سرور بیرونی تماس در دسترس نیست یا پاسخ 202 نداده است.', type: 'array{message:string}')]
    public function __invoke(Request $request, int $appointment, PractitionerCallService $calls): JsonResponse { return response()->json(['data' => $calls->autoCall($request->attributes->get('practitioner'), $appointment)], 202); }
}
