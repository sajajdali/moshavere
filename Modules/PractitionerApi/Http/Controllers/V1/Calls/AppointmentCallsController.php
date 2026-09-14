<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Calls;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Modules\PractitionerApi\Services\PractitionerCallService;
#[Group('تماس و VoIP', 'تماس‌های متعلق به پزشک جاری', weight: 6)] class AppointmentCallsController {
    /** فهرست تماس‌های یک نوبت؛ زمان‌ها ISO-8601 و duration_seconds برحسب ثانیه است. */
    #[ApiResponse(200, 'تماس‌ها و جمع کل.', type: 'array{data: list<array{id:int, sequence:int, direction:string, started_at:string|null, answered_at:string|null, ended_at:string|null, duration_seconds:int, result:string, early:bool, ended_by:string, channel:string, note:string|null}>, meta: array{total:int, answered:int, missed:int, talk_seconds:int}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    public function __invoke(Request $request, int $appointment, PractitionerCallService $calls): JsonResponse { return response()->json($calls->calls($request->attributes->get('practitioner'), $appointment)); }
}
