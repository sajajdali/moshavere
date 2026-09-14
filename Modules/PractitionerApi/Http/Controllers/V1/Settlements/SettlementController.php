<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Settlements;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerSettlementService;

#[Group('تسویه و مالی', 'همه مبالغ integer و برحسب تومان هستند', weight: 9)]
class SettlementController
{
    /** محاسبه تازه تسویه؛ زمان مکالمه ثانیه و زمان رزرو/استفاده‌نشده دقیقه است. */
    #[ApiResponse(200, 'جزئیات محاسبه تسویه.', type: 'array{data: array{id:int,appointment_id:int,status:string,finalized:bool,can_confirm:bool,reserved_minutes:int,raw_talk_seconds:int,ignored_talk_seconds:int,connection_overhead_minutes:int,billable_talk_seconds:int,system_unused_minutes:int,approved_unused_minutes:int,manual_adjustment_requires_reason:bool,total_paid_amount:int,suggested_refund_amount:int,effective_refund_amount:int,used_amount:int,practitioner_earned_amount:int,platform_profit_amount:int,currency:string,approved_at:string|null,wallet_transaction_id:int|null,audit_count:int}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'اطلاعات مالی قابل محاسبه نیست.', type: 'array{message:string,errors:array<string,list<string>>}')]
    public function __invoke(Request $request, int $appointment, PractitionerSettlementService $settlements): JsonResponse
    {
        return response()->json(['data' => $settlements->show($request->attributes->get('practitioner'), $appointment)]);
    }
}
