<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Reports;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Reports\StoreReportRequest;
use Modules\PractitionerApi\Services\PractitionerReportService;

#[Group('گزارش و پرونده بیمار', 'گزارش‌های پزشکی متعلق به پزشک جاری', weight: 7)]
class StoreAppointmentReportController
{
    /** ثبت گزارش در پرونده باز؛ follow_up_at تاریخ‌وساعت ISO-8601 است. */
    #[ApiResponse(201, 'گزارش ثبت شد.', type: 'array{data: array{id:int, appointment_id:int, outcome:string, outcome_label:string, subject:string, report_text:string, follow_up_at:string, created_at:string|null, author:array{id:int,name:string|null}|null}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'ورودی نامعتبر یا پرونده بسته است.', type: 'array{message:string, errors:array<string,list<string>>}')]
    public function __invoke(StoreReportRequest $request, int $appointment, PractitionerReportService $reports): JsonResponse
    {
        return response()->json(['data' => $reports->create($request->attributes->get('practitioner'), $appointment, $request->validated())], 201);
    }
}
