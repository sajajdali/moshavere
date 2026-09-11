<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultationCallbackService;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class ConsultationCallbackController extends Controller
{
    public function store(Request $request, AppointmentUser $appointment, ConsultationCallbackService $service)
    {
        if (! ConsultationAccess::schemaReady(['appointment_callback_requests'])) {
            return back()->withErrors(['callback' => 'جدول ثبت درخواست تماس هنوز نصب نشده است؛ migration ماژول مشاوره باید اجرا شود.']);
        }

        abort_unless(
            (int) $request->user()->id === (int) $appointment->doctor_id || $request->user()->can('SUPER_ADMIN'),
            403,
            'درخواست تماس فقط برای مشاور همین نوبت یا مدیر کل مجاز است.'
        );
        abort_if($appointment->trashed(), 422, 'نوبت حذف شده است.');
        abort_unless($appointment->kind === AppointmentUserKindEnum::VOIP, 422, 'درخواست تماس فقط برای نوبت تلفنی مجاز است.');
        abort_if($appointment->status === AppointmentUserStatusEnum::STATUS_CANCEL, 422, 'نوبت لغوشده قابل تماس نیست.');
        abort_if($appointment->consultationCase && $appointment->consultationCase->state !== 'OPEN', 422, 'پرونده این مشاوره بسته شده است.');

        $appointment->loadMissing('user');
        $practitioner = ConsultationPractitioner::where('user_id', $appointment->doctor_id)
            ->where('active', true)->first();
        if (! $practitioner) {
            return back()->withErrors(['callback' => 'پروفایل فعال مشاور برای این نوبت پیدا نشد.']);
        }

        try {
            $callback = $service->request($appointment, $practitioner, $request->user());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['callback' => $exception->getMessage()]);
        }

        return back()->with('success', 'درخواست تماس با شناسه '.$callback->request_id.' برای سرور ارسال شد.');
    }
}
