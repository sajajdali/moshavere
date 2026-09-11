<?php

namespace Modules\Api\Http\Controllers\Voip;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer;

class ConsultantNoAnswerController extends Controller
{
    use ApiHandlerTrait;

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'call_id' => ['required', 'string', 'max:100'],
            'appointment_id' => ['required', 'integer', 'exists:appointment_users,id'],
            'no_answer_at' => ['required', 'date'],
            'ring_started_at' => ['nullable', 'date', 'before_or_equal:no_answer_at'],
            'ring_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'extension' => ['nullable', 'string', 'max:30'],
            'channel' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->badRequest([
                'status' => false,
                'error_code' => VoipResponseCode::INVALID_CONSULTANT_NO_ANSWER,
                'message' => 'اطلاعات عدم پاسخ‌گویی مشاور نامعتبر است.',
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $data = $validator->validated();
        $data['ring_duration_seconds'] = $data['ring_duration_seconds'] ?? 0;
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


            $callLog = AppointmentCallLog::where('call_id', $data['call_id'])->first();
            if ($callLog?->isEarlyCall()) {
                AppointmentConsultantNoAnswer::where('call_id', $data['call_id'])->delete();

                return $this->ok([
                    'status' => true,
                    'error_code' => VoipResponseCode::SUCCESS,
                    'message' => 'تماس پیش از شروع نوبت بوده و به‌عنوان عدم پاسخ مشاور ثبت نشد.',
                    'call_id' => $data['call_id'],
                    'appointment_id' => $data['appointment_id'],
                    'ignored_as_early_call' => true,
                ]);
            }

            $noAnswer = AppointmentConsultantNoAnswer::updateOrCreate(
                ['call_id' => $data['call_id']],
                $data
            );

            return $this->ok([
                'status' => true,
                'error_code' => VoipResponseCode::SUCCESS,
                'message' => $noAnswer->wasRecentlyCreated
                    ? 'عدم پاسخ‌گویی مشاور با موفقیت ثبت شد.'
                    : 'عدم پاسخ‌گویی مشاور به‌روزرسانی شد.',
                'call_id' => $noAnswer->call_id,
                'appointment_id' => $noAnswer->appointment_id,
                'no_answer_at' => $noAnswer->no_answer_at?->toIso8601String(),
                'ring_duration_seconds' => $noAnswer->ring_duration_seconds,
            ]);
        });
    }
}
