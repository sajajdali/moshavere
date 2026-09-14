<?php

namespace Modules\Api\Http\Controllers\Voip;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Front\app\Models\FeedBack;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentConsultantHangup;
use Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer;
use Modules\OnlineConsultation\Models\AppointmentCallbackRequest;
use Modules\OnlineConsultation\Support\ConsultationAccess;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\User\Notifications\UserMessageNotification;

class CallLogController extends Controller
{
    use ApiHandlerTrait;

    private const RESULTS = ['NOT_DIALED', 'CALLER_ABANDONED', 'ANSWERED', 'NOANSWER', 'BUSY', 'CHANUNAVAIL', 'CONGESTION', 'FAILED', 'MISSING_EXTENSION'];
    private const STATES = ['NO_APPOINTMENT', 'BEFORE_APPOINTMENT', 'WAITING_FOR_APPOINTMENT', 'IN_APPOINTMENT_TIME', 'APPOINTMENT_EXPIRED', 'API_ERROR'];
    private const CONNECTIONS = ['NONE', 'DIRECT', 'DIVERTED'];
    private const DISCONNECTORS = ['PATIENT', 'DOCTOR', 'SYSTEM', 'UNKNOWN'];

    public function store(Request $request)
    {
        $payload = $request->all();
        if (! array_key_exists('survey_score', $payload)) {
            $nestedScore = data_get($payload, 'additional_data.survey_score')
                ?? data_get($payload, 'additional_data.score')
                ?? data_get($payload, 'additional_data.rating');
            if ($nestedScore !== null) {
                $payload['survey_score'] = $nestedScore;
            }
        }

        $validator = validator($payload, [
            'call_id' => ['required', 'string', 'max:100'],
            'request_id' => ['nullable', 'string', 'max:120'],
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
            'survey_score' => ['nullable', 'integer', 'between:1,5'],
            'score' => ['nullable', 'integer', 'between:1,5'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);
        if ($validator->fails()) {
            return $this->badRequest(['status' => false, 'error_code' => VoipResponseCode::INVALID_CALL_LOG, 'message' => 'اطلاعات گزارش تماس نامعتبر است.', 'errors' => $validator->errors()->toArray()]);
        }
        $data = $validator->validated();
        $callbackRequestId = $data['request_id'] ?? data_get($data, 'additional_data.request_id');
        unset($data['request_id']);
        if ($callbackRequestId !== null) {
            $data['additional_data'] = array_merge($data['additional_data'] ?? [], ['request_id' => $callbackRequestId]);
        }
        $surveyScore = $this->surveyScore($data);
        unset($data['survey_score'], $data['score'], $data['rating']);
        if ($surveyScore !== null) {
            $data['additional_data'] = array_merge($data['additional_data'] ?? [], [
                'survey_score' => $surveyScore,
            ]);
        }
        $data['raw_payload'] = $request->all();
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $surveyScore, $callbackRequestId) {
            if (! empty($data['appointment_id'])) {
                AppointmentUser::lockForUpdate()->findOrFail($data['appointment_id']);
            }
            $existingLog = AppointmentCallLog::where('call_id', $data['call_id'])->first();
            if ($existingLog) {
                $data['additional_data'] = array_merge(
                    $existingLog->additional_data ?? [],
                    $data['additional_data'] ?? []
                );
                $surveyScore ??= $existingLog->surveyScore();
            }
            $consultantHangup = AppointmentConsultantHangup::where('call_id', $data['call_id'])->first();
            if ($consultantHangup) {
                // The explicit consultant-hangup endpoint takes precedence over a
                // generic or incorrectly attributed PBX hangup value.
                $data['disconnected_by'] = 'DOCTOR';
                $data['ended_at'] = $consultantHangup->hung_up_at;
            }
            $data['destination'] = $data['connected_destination'] ?? $data['primary_extension'] ?? null;
            $isUpdate = $existingLog !== null;
            $log = AppointmentCallLog::updateOrCreate(['call_id' => $data['call_id']], $data);
            if ($callbackRequestId && ConsultationAccess::schemaReady(['appointment_callback_requests'])) {
                AppointmentCallbackRequest::where('request_id', $callbackRequestId)
                    ->when($log->appointment_id, fn ($query, $appointmentId) => $query->where('appointment_id', $appointmentId))
                    ->update(['call_id' => $log->call_id, 'final_call_received_at' => now()]);
            }
            if ($log->isEarlyCall()) {
                // A pre-appointment attempt is informational, never a consultant
                // no-answer incident, even if events arrive out of order.
                AppointmentConsultantNoAnswer::where('call_id', $log->call_id)->delete();
            }
            $consultantHangup = AppointmentConsultantHangup::where('call_id', $log->call_id)->first();
            if ($consultantHangup && ($log->disconnected_by !== 'DOCTOR' || ! $log->ended_at?->equalTo($consultantHangup->hung_up_at))) {
                $log->forceFill([
                    'disconnected_by' => 'DOCTOR',
                    'ended_at' => $consultantHangup->hung_up_at,
                ])->save();
            }
            if ($log->appointment_id) {
                $case = $log->appointment->consultationCase;
                if ($case?->state === 'PATIENT_NO_SHOW' && ($log->direction !== 'OUTBOUND' || $log->final_result === 'ANSWERED' || $log->answered_at || $log->talk_duration_seconds > 0)) {
                    $case->events()->firstOrCreate([
                        'action' => 'NO_SHOW_CALL_RECEIVED',
                        'reason' => 'گزارش تماس '.$log->call_id.' پس از ثبت عدم حضور دریافت شد؛ بررسی اعتراض و تطبیق زمان تماس الزامی است. مبالغ تسویه خودکار تغییر نکردند.',
                    ], ['snapshot' => ['call_id' => $log->call_id, 'call_entered_at' => $log->call_entered_at?->toIso8601String(), 'final_result' => $log->final_result, 'received_at' => now()->toIso8601String()]]);
                }
                $this->syncAppointmentSurvey($log->appointment_id, $surveyScore);
                $billing = app(AppointmentBillingService::class)->ensure($log->appointment);
                if ($billing) app(AppointmentBillingService::class)->refresh($billing);
            }
            if (! $isUpdate && $log->appointment?->doctor) {
                \Illuminate\Support\Facades\DB::afterCommit(function () use ($log): void {
                    try {
                        $log->appointment->doctor->notify(new UserMessageNotification(
                            'وضعیت تماس مشاوره',
                            $log->final_result === 'ANSWERED' ? 'تماس مشاوره با موفقیت برقرار شد.' : 'نتیجه تماس مشاوره ثبت شد.',
                            'نتیجه تماس در پرونده نوبت ثبت شده است. برای مشاهده جزئیات، اپ را باز کنید.',
                            ['type' => 'voip_call_result', 'appointment_id' => $log->appointment_id, 'call_id' => $log->call_id],
                            '/appointments/'.$log->appointment_id,
                        ));
                    } catch (\Throwable $notificationException) {
                        report($notificationException);
                    }
                });
            }
            return $this->ok([
                'status' => true,
                'error_code' => VoipResponseCode::SUCCESS,
                'message' => $isUpdate ? 'گزارش تماس به‌روزرسانی شد.' : 'گزارش تماس با موفقیت ثبت شد.',
                'call_id' => $log->call_id,
                'request_id' => $callbackRequestId,
                'appointment_id' => $log->appointment_id,
                'final_result' => $log->final_result,
                'disconnected_by' => $log->disconnected_by,
                'answered_at' => $log->answered_at?->toIso8601String(),
                'ended_at' => $log->ended_at?->toIso8601String(),
                'talk_duration_seconds' => (int) $log->talk_duration_seconds,
                'connection_type' => $log->connection_type,
                'connection_attempts_count' => count($log->attempts ?? []),
                'consultant_hangup_recorded' => $log->consultantHangup()->exists(),
                'survey_score' => $log->surveyScore(),
            ]);
        });
    }

    private function surveyScore(array $data): ?int
    {
        foreach (['survey_score', 'score', 'rating', 'additional_data.survey_score', 'additional_data.score', 'additional_data.rating'] as $key) {
            $value = data_get($data, $key);
            if ($value !== null) {
                return (int) $value;
            }
        }

        return null;
    }

    private function syncAppointmentSurvey(int $appointmentId, ?int $surveyScore): void
    {
        if ($surveyScore === null) {
            return;
        }

        $appointment = AppointmentUser::find($appointmentId);
        if (! $appointment) {
            return;
        }

        $details = $appointment->details ?? [];
        $survey = (array) data_get($details, AppointmentUser::DETAIL_SURVEY, []);
        $survey[AppointmentUser::DETAIL_SURVEY] = $surveyScore;
        $details[AppointmentUser::DETAIL_SURVEY] = $survey;
        $appointment->update(['details' => $details]);

        if (Schema::hasTable('feedbacks')) {
            FeedBack::updateOrCreate(
                ['appointment_user_id' => $appointmentId, 'question' => 1],
                ['answer' => $surveyScore]
            );
        }
    }
}
