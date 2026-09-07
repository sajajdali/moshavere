<?php

namespace Modules\Api\Http\Controllers\Voip;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Services\AppointmentBillingService;

class CallLogController extends Controller
{
    use ApiHandlerTrait;

    private const RESULTS = ['NOT_DIALED', 'CALLER_ABANDONED', 'ANSWERED', 'NOANSWER', 'BUSY', 'CHANUNAVAIL', 'CONGESTION', 'FAILED', 'MISSING_EXTENSION'];
    private const STATES = ['NO_APPOINTMENT', 'BEFORE_APPOINTMENT', 'WAITING_FOR_APPOINTMENT', 'IN_APPOINTMENT_TIME', 'APPOINTMENT_EXPIRED', 'API_ERROR'];
    private const CONNECTIONS = ['NONE', 'DIRECT', 'DIVERTED'];
    private const DISCONNECTORS = ['PATIENT', 'DOCTOR', 'SYSTEM', 'UNKNOWN'];

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'call_id' => ['required', 'string', 'max:100'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointment_users,id'],
            'patient_phone' => ['required', 'string', 'max:40'],
            'operator_id' => ['nullable', 'integer', 'exists:users,id'],
            'appointment_start_at' => ['nullable', 'date'], 'appointment_end_at' => ['nullable', 'date'],
            'call_entered_at' => ['nullable', 'date'], 'dial_started_at' => ['nullable', 'date'],
            'answered_at' => ['nullable', 'date'], 'ended_at' => ['nullable', 'date'],
            'appointment_state' => ['required', 'string', 'in:'.implode(',', self::STATES)],
            'final_result' => ['required', 'string', 'in:'.implode(',', self::RESULTS)],
            'connection_type' => ['required', 'string', 'in:'.implode(',', self::CONNECTIONS)],
            'primary_extension' => ['nullable', 'string', 'max:30'], 'connected_destination' => ['nullable', 'string', 'max:80'],
            'wait_duration_seconds' => ['nullable', 'integer', 'min:0'], 'ring_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'talk_duration_seconds' => ['nullable', 'integer', 'min:0'], 'total_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'disconnected_by' => ['nullable', 'string', 'in:'.implode(',', self::DISCONNECTORS)],
            'hangup_cause' => ['nullable', 'integer', 'min:0'], 'responded_by' => ['nullable', 'string', 'max:255'],
            'direction' => ['nullable', 'string', 'in:INBOUND,OUTBOUND'],
            'additional_data' => ['nullable', 'array'], 'attempts' => ['nullable', 'array'],
        ]);
        if ($validator->fails()) {
            return $this->badRequest(['status' => false, 'error_code' => VoipResponseCode::INVALID_CALL_LOG, 'message' => 'اطلاعات گزارش تماس نامعتبر است.', 'errors' => $validator->errors()->toArray()]);
        }
        $data = $validator->validated();
        $data['raw_payload'] = $request->all();
        $data['is_update'] = AppointmentCallLog::where('call_id', $data['call_id'])->exists();
        $data['destination'] = $data['connected_destination'] ?? $data['primary_extension'] ?? null;
        $isUpdate = $data['is_update'];
        unset($data['is_update']);
        $log = AppointmentCallLog::updateOrCreate(['call_id' => $data['call_id']], $data);
        if ($log->appointment_id) {
            $billing = app(AppointmentBillingService::class)->ensure($log->appointment);
            if ($billing) app(AppointmentBillingService::class)->refresh($billing);
        }
        return $this->ok([
            'status' => true,
            'error_code' => VoipResponseCode::SUCCESS,
            'message' => $isUpdate ? 'گزارش تماس به‌روزرسانی شد.' : 'گزارش تماس با موفقیت ثبت شد.',
            'call_id' => $log->call_id,
            'appointment_id' => $log->appointment_id,
        ]);
    }
}
