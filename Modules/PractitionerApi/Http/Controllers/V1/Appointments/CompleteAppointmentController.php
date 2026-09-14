<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Appointments;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Appointments\CompleteAppointmentRequest;
use Modules\PractitionerApi\Services\PractitionerAppointmentActionService;

#[Group('عملیات نهایی نوبت', 'عملیات حساس و idempotent متعلق به پزشک جاری', weight: 8)]
class CompleteAppointmentController
{
    /** پایان مشاوره؛ تأیید صریح و حداقل یک گزارش لازم است. ارسال مجدد نتیجه قبلی را برمی‌گرداند. */
    #[ApiResponse(200, 'پرونده تکمیل و جزئیات تازه نوبت بازگردانده شد.', type: 'array{data: array{operation:string, case_state:string, idempotent:bool, appointment:array<string,mixed>}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'تأیید ارسال نشده، نوبت معتبر نیست یا گزارش ندارد.', type: 'array{message:string, errors:array<string,list<string>>}')]
    public function __invoke(CompleteAppointmentRequest $request, int $appointment, PractitionerAppointmentActionService $actions): JsonResponse
    {
        return response()->json(['data' => $actions->complete($request->attributes->get('practitioner'), $appointment)]);
    }
}
