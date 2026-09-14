<?php
namespace Modules\PractitionerApi\Http\Controllers\V1\Voip;
use Dedoc\Scramble\Attributes\Group; use Dedoc\Scramble\Attributes\Response as ApiResponse; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Modules\PractitionerApi\Services\PractitionerSoftphoneService;
#[Group('تماس و VoIP', 'تنظیمات ثبت Softphone پزشک', weight: 6)] class VoipConfigController {
    /** تنظیمات کامل SIP؛ host/port/transport ثابت Tenant و credentials مختص پزشک است. password محرمانه و ممنوع از log است. */
    #[ApiResponse(200, 'قرارداد ثبت Softphone.', type: 'array{data: array{configured:bool, server_address:string|null, server_host:string|null, server_port:int, transport:string, extension:string|null, username:string|null, password:string|null, missing_fields:list<string>}}')]
    public function __invoke(Request $request, PractitionerSoftphoneService $softphone): JsonResponse { return response()->json(['data' => $softphone->for($request->attributes->get('practitioner'))]); }
}
