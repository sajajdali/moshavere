<?php

namespace Modules\PractitionerApi\Http\Controllers\V1\Devices;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\PractitionerApi\Http\Requests\V1\Devices\UpdateCurrentDeviceRequest;
use Modules\PractitionerApi\Services\PractitionerDeviceService;

#[Group('دستگاه و اعلان', 'مدیریت نصب جاری اپ پزشک', weight: 11)]
class UpdateCurrentDeviceController
{
    /** ثبت، تعویض یا پاک‌کردن FCM token همان نصب؛ برای پاک‌کردن fcm_token برابر null بفرستید. */
    #[ApiResponse(200, 'وضعیت اعلان دستگاه.', type: 'array{data: array{device_identifier:string,notifications_enabled:bool,device_version:string|null,updated_at:string|null}}')]
    #[ApiResponse(404, 'نصب متعلق به token جاری نیست یا حذف شده است.', type: 'array{message:string}')]
    #[ApiResponse(422, 'شناسه دستگاه یا FCM token نامعتبر است.', type: 'array{message:string,errors:array<string,list<string>>}')]
    public function __invoke(UpdateCurrentDeviceRequest $request, PractitionerDeviceService $devices): JsonResponse
    {
        return response()->json(['data' => $devices->update($request->user(), $request->validated())]);
    }
}
