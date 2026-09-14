<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Voip;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Modules\PractitionerApi\Http\Requests\V1\Voip\UpdateVoipConfigRequest; use Modules\PractitionerApi\Services\PractitionerCallService;
#[Group('تماس و VoIP', 'تنظیمات ثبت Softphone پزشک', weight: 6)] class UpdateVoipConfigController {
    /** ویرایش credentials پزشک؛ password خالی/ارسال‌نشده رمز قبلی را حفظ می‌کند. تنظیم ثابت سرور از اپ قابل تغییر نیست. */
    #[ApiResponse(200, 'تنظیمات تازه Softphone.', type: 'array{data: array{configured:bool, server_address:string|null, server_host:string|null, server_port:int, transport:string, extension:string|null, username:string|null, password:string|null, missing_fields:list<string>}}')]
    #[ApiResponse(422, 'داخلی تکراری یا ورودی نامعتبر.', type: 'array{message:string, errors:array<string,list<string>>}')]
    public function __invoke(UpdateVoipConfigRequest $request, PractitionerCallService $calls): JsonResponse { return response()->json(['data' => $calls->updateVoip($request->attributes->get('practitioner'), $request->validated())]); }
}
