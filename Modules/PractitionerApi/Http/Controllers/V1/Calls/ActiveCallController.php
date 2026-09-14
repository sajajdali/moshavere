<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Calls;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Modules\PractitionerApi\Services\PractitionerCallService;
#[Group('تماس و VoIP', 'تماس‌های متعلق به پزشک جاری', weight: 6)] class ActiveCallController {
    /** آخرین تماس زنده پزشک؛ نبود تماس فعال با data برابر null پاسخ داده می‌شود. */
    #[ApiResponse(200, 'تماس فعال یا null.', type: 'array{data: array{id:int, sequence:int, appointment_id:int, direction:string, started_at:string|null, answered_at:string|null, ended_at:string|null, duration_seconds:int, result:string, early:bool, ended_by:string, channel:string, note:string|null}|null}')]
    public function __invoke(Request $request, PractitionerCallService $calls): JsonResponse { return response()->json(['data' => $calls->active($request->attributes->get('practitioner'))]); }
}
