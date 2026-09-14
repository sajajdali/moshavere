<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Settlements;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Settlements\ConfirmSettlementRequest;
use Modules\PractitionerApi\Services\PractitionerSettlementService;

#[Group('تسویه و مالی', 'همه مبالغ integer و برحسب تومان هستند', weight: 9)]
class ConfirmSettlementController
{
    /** تأیید نهایی و غیرقابل‌ویرایش؛ اختلاف با زمان سیستم فقط با reason ثبت می‌شود. */
    #[ApiResponse(200, 'تسویه نهایی، نتیجه idempotency و جزئیات تازه نوبت.', type: 'array{data: array{idempotent:bool,settlement:array<string,mixed>,appointment:array<string,mixed>}}')]
    #[ApiResponse(404, 'نوبت متعلق به پزشک نیست.', type: 'array{message:string}')]
    #[ApiResponse(422, 'تأیید، زمان، دلیل، پرداخت یا وضعیت نوبت معتبر نیست.', type: 'array{message:string,errors:array<string,list<string>>}')]
    public function __invoke(ConfirmSettlementRequest $request, int $appointment, PractitionerSettlementService $settlements): JsonResponse
    {
        return response()->json(['data' => $settlements->confirm($request->attributes->get('practitioner'), $appointment, $request->validated())]);
    }
}
