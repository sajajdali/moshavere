<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Patients;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PractitionerApi\Services\PractitionerReportService;

#[Group('گزارش و پرونده بیمار', 'تاریخچه محدود به رابطه بیمار با پزشک جاری', weight: 7)]
class PatientHistoryController
{
    /** تاریخچه نوبت‌ها و گزارش‌های همین پزشک؛ شناسه بیمار بدون سابقه مشترک پاسخ 404 می‌دهد. */
    #[ApiResponse(200, 'تاریخچه بیمار.', type: 'array{data: array{patient: array{id:int,name:string|null,mobile:string|null}, appointments:list<array{id:int,file_no:string|null,visited_at:string|null,service:array{id:int,title:string}|null,reports:list<array{id:int,appointment_id:int,outcome:string,outcome_label:string,subject:string,report_text:string,follow_up_at:string|null,created_at:string|null,author:array{id:int,name:string|null}|null}>}>}}')]
    #[ApiResponse(404, 'بیمار در سوابق این پزشک نیست.', type: 'array{message:string}')]
    public function __invoke(Request $request, int $patient, PractitionerReportService $reports): JsonResponse
    {
        return response()->json(['data' => $reports->patientHistory($request->attributes->get('practitioner'), $patient)]);
    }
}
