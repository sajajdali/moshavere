<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Appointments;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Appointments\AppointmentIndexRequest;
use Modules\PractitionerApi\Services\PractitionerAppointmentService;

#[Group('نوبت‌ها', 'فهرست و جزئیات نوبت‌های متعلق به پزشک جاری', weight: 5)]
class AppointmentIndexController
{
    /**
     * فهرست نوبت‌های پزشک
     *
     * scope یکی از today، tomorrow، past و all است و پیش‌فرض today است. date بر
     * scope اولویت دارد. جست‌وجو روی موبایل و نام ذخیره‌شده بیمار انجام می‌شود.
     * per_page از 1 تا 100 و پیش‌فرض 20 است. مبالغ تومان و مدت تماس ثانیه هستند.
     */
    #[ApiResponse(200, 'فهرست صفحه‌بندی‌شده نوبت‌های همین پزشک. phase یکی از upcoming، in_progress، past یا unknown و settlement_status یکی از settled، refundable یا unsettled است.', type: 'array{data: list<array{id: int, file_no: string|null, starts_at: string|null, ends_at: string|null, duration_minutes: int, phase: string, status: string, status_reason: string|null, countdown: array{starts_in_seconds: int|null, ends_in_seconds: int|null}, patient: array{id: int|null, full_name: string|null, mobile: string|null}, section: array{key: string, name: string}, complaint_summary: string|null, calls_summary: array{total: int, answered: int, unanswered: int, talk_seconds: int}, settlement_status: string, paid: bool, reports_count: int}>, links: array{first: string|null, last: string|null, prev: string|null, next: string|null}, meta: array{current_page: int, from: int|null, last_page: int, path: string, per_page: int, to: int|null, total: int}}')]
    #[ApiResponse(401, 'توکن معتبر نیست.', type: 'array{message: string}')]
    #[ApiResponse(403, 'دسترسی پزشک فعال نیست.', type: 'array{message: string, errors: array<string, list<string>>}')]
    #[ApiResponse(422, 'scope، date، page یا per_page معتبر نیست.', type: 'array{message: string, errors: array<string, list<string>>}')]
    public function __invoke(AppointmentIndexRequest $request, PractitionerAppointmentService $appointments): JsonResponse
    {
        $result = $appointments->paginate($request->attributes->get('practitioner'), $request->validated());
        $paginator = $result['paginator'];
        return response()->json([
            'data' => $result['items'],
            'links' => ['first' => $paginator->url(1), 'last' => $paginator->url($paginator->lastPage()), 'prev' => $paginator->previousPageUrl(), 'next' => $paginator->nextPageUrl()],
            'meta' => ['current_page' => $paginator->currentPage(), 'from' => $paginator->firstItem(), 'last_page' => $paginator->lastPage(), 'path' => $paginator->path(), 'per_page' => $paginator->perPage(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()],
        ]);
    }
}
