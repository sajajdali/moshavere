<?php

namespace Modules\Api\Http\Controllers\Voip;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentConsultantHangup;

class ConsultantHangupController extends Controller
{
    use ApiHandlerTrait;

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'call_id' => ['required', 'string', 'max:100'],
            'appointment_id' => ['required', 'integer', 'exists:appointment_users,id'],
            'hung_up_at' => ['required', 'date'],
            'hangup_via' => ['required', 'string', 'in:PHONE,SOFTPHONE'],
            'extension' => ['nullable', 'string', 'max:30'],
            'channel' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->badRequest([
                'status' => false,
                'error_code' => VoipResponseCode::INVALID_CONSULTANT_HANGUP,
                'message' => 'اطلاعات قطع تماس مشاور نامعتبر است.',
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $data = $validator->validated();
        $data['hangup_via'] = strtoupper($data['hangup_via']);
        $data['raw_payload'] = $request->all();
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $appointment = \Modules\AppointmentUser\app\Models\AppointmentUser::lockForUpdate()->findOrFail($data['appointment_id']);
            $case = $appointment->consultationCase;
            if ($case?->state === 'PATIENT_NO_SHOW') {
                $case->events()->firstOrCreate([
                    'action' => 'NO_SHOW_CALL_RECEIVED',
                    'reason' => 'رویداد تماس '.$data['call_id'].' پس از ثبت عدم حضور دریافت شد؛ بررسی اعتراض و تطبیق زمان تماس الزامی است.',
                ], ['snapshot' => $data]);
            }


            $hangup = AppointmentConsultantHangup::updateOrCreate(
                ['call_id' => $data['call_id']],
                $data
            );

            // This dedicated event is authoritative. Never leave the general call
            // report marked as disconnected by the patient/user for the same call.
            AppointmentCallLog::where('call_id', $hangup->call_id)->update([
                'disconnected_by' => 'DOCTOR',
                'ended_at' => $hangup->hung_up_at,
            ]);

            return $this->ok([
                'status' => true,
                'error_code' => VoipResponseCode::SUCCESS,
                'message' => $hangup->wasRecentlyCreated
                    ? 'قطع تماس مشاور با موفقیت ثبت شد.'
                    : 'قطع تماس مشاور به‌روزرسانی شد.',
                'call_id' => $hangup->call_id,
                'appointment_id' => $hangup->appointment_id,
                'hung_up_at' => $hangup->hung_up_at?->toIso8601String(),
                'hangup_via' => $hangup->hangup_via,
                'disconnected_by' => 'DOCTOR',
            ]);
        });
    }
}
