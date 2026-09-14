<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Appointments;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Appointments\MarkNoShowRequest;
use Modules\PractitionerApi\Services\PractitionerAppointmentActionService;

#[Group('عملیات نهایی نوبت', 'عملیات حساس و idempotent متعلق به پزشک جاری', weight: 8)]
class MarkNoShowController
{
    /** ثبت عدم حضور؛ فقط پس از پایان بازه و نبود هرگونه تماس بیمار یا تماس پاسخ‌داده‌شده مجاز است. */
    #[ApiResponse(200, 'عدم حضور و تسویه متناظر ثبت شد.', type: 'array{data: array{operation:string, case_state:string, idempotent:bool, appointment:array<string,mixed>}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'تأیید/شرایط زمانی/تماس/اطلاعات مالی معتبر نیست.', type: 'array{message:string, errors:array<string,list<string>>}')]
    public function __invoke(MarkNoShowRequest $request, int $appointment, PractitionerAppointmentActionService $actions): JsonResponse
    {
        return response()->json(['data' => $actions->noShow($request->attributes->get('practitioner'), $appointment)]);
    }
}
