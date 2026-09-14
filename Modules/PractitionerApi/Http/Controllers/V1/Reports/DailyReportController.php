<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Reports;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Reports\DailyReportRequest;
use Modules\PractitionerApi\Services\PractitionerDailyReportService;

#[Group('گزارش روزانه', 'گزارش عملیاتی و مالی پزشک جاری', weight: 10)]
class DailyReportController
{
    /** گزارش امروز، تاریخ مشخص یا هفت روز اخیر؛ تاریخ میلادی Y-m-d، مدت تماس ثانیه و مبلغ تومان است. */
    #[ApiResponse(200, 'جمع بازه و تفکیک تمام روزها، حتی روز خالی.', type: 'array{data: array{scope:string,timezone:string,from:string,to:string,currency:string,totals:array{appointments:int,unique_patients:int,completed:int,patient_no_show:int,open:int,missed_appointments:int,reserved_minutes:int,calls:int,inbound:int,outbound:int,answered:int,unanswered:int,early_calls:int,call_results:array<string,int>,raw_talk_seconds:int,ignored_talk_seconds:int,billable_talk_seconds:int,talk_minutes:int,settled:int,unsettled:int,finalized_gross_amount:int,pending_gross_amount:int,refunded_amount:int,pending_refund_amount:int,net_amount:int,practitioner_income_amount:int,platform_profit_amount:int},days:list<array{date:string,metrics:array{appointments:int,unique_patients:int,completed:int,patient_no_show:int,open:int,missed_appointments:int,reserved_minutes:int,calls:int,inbound:int,outbound:int,answered:int,unanswered:int,early_calls:int,call_results:array<string,int>,raw_talk_seconds:int,ignored_talk_seconds:int,billable_talk_seconds:int,talk_minutes:int,settled:int,unsettled:int,finalized_gross_amount:int,pending_gross_amount:int,refunded_amount:int,pending_refund_amount:int,net_amount:int,practitioner_income_amount:int,platform_profit_amount:int}}>}}')]
    #[ApiResponse(422, 'scope یا تاریخ نامعتبر است.', type: 'array{message:string,errors:array<string,list<string>>}')]
    public function __invoke(DailyReportRequest $request, PractitionerDailyReportService $reports): JsonResponse
    {
        return response()->json(['data' => $reports->report($request->attributes->get('practitioner'), $request->validated())]);
    }
}
