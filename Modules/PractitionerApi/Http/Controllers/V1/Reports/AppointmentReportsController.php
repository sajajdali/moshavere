<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Reports;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerReportService;

#[Group('گزارش و پرونده بیمار', 'گزارش‌های پزشکی متعلق به پزشک جاری', weight: 7)]
class AppointmentReportsController
{
    /** گزارش‌های نوبت و وضعیت پرونده؛ نتیجه‌ها برای ساخت فرم کلاینت نیز برگردانده می‌شوند. */
    #[ApiResponse(200, 'فهرست گزارش‌ها.', type: 'array{data: array{case: array{state:string, closed:bool}, reports:list<array{id:int, appointment_id:int, outcome:string, outcome_label:string, subject:string, report_text:string, follow_up_at:string|null, created_at:string|null, author:array{id:int,name:string|null}|null}>, outcomes:list<array{value:string,label:string}>}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    public function __invoke(Request $request, int $appointment, PractitionerReportService $reports): JsonResponse
    {
        return response()->json(['data' => $reports->reports($request->attributes->get('practitioner'), $appointment)]);
    }
}
