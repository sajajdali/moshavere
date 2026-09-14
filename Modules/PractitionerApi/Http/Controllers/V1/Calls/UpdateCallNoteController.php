<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Calls;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Modules\PractitionerApi\Http\Requests\V1\Calls\UpdateCallNoteRequest; use Modules\PractitionerApi\Services\PractitionerCallService;
#[Group('تماس و VoIP', 'تماس‌های متعلق به پزشک جاری', weight: 6)] class UpdateCallNoteController {
    /** ثبت یا پاک‌کردن یادداشت تماس؛ حداکثر 3000 نویسه و فقط برای تماس خود پزشک. */
    #[ApiResponse(200, 'یادداشت ذخیره شد.', type: 'array{data: array{id:int, appointment_id:int, note:string|null}}')]
    #[ApiResponse(404, 'تماس متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'یادداشت بیش از حد مجاز است.', type: 'array{message:string, errors:array<string,list<string>>}')]
    public function __invoke(UpdateCallNoteRequest $request, int $call, PractitionerCallService $calls): JsonResponse { return response()->json(['data' => $calls->note($request->attributes->get('practitioner'), $call, $request->validated('note'))]); }
}
