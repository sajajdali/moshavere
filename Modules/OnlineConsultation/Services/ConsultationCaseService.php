<?php

namespace Modules\OnlineConsultation\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentConsultationCase;
use Modules\OnlineConsultation\Models\AppointmentConsultationReport;
use Modules\User\Entities\User;

class ConsultationCaseService
{
    public function ensure(AppointmentUser $appointment): AppointmentConsultationCase
    {
        return AppointmentConsultationCase::firstOrCreate(
            ['appointment_id' => $appointment->id],
            ['state' => AppointmentConsultationCase::STATE_OPEN]
        );
    }

    public function addReport(AppointmentUser $appointment, User $actor, array $data): AppointmentConsultationReport
    {
        return DB::transaction(function () use ($appointment, $actor, $data) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            $case = AppointmentConsultationCase::lockForUpdate()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['state' => AppointmentConsultationCase::STATE_OPEN]
            );
            if ($case->isClosed()) {
                throw ValidationException::withMessages(['case' => 'این مشاوره تمام شده است؛ برای ثبت گزارش جدید ابتدا آن را باز کنید.']);
            }

            return $case->reports()->create([
                'appointment_id' => $appointment->id,
                'author_id' => $actor->id,
                'outcome' => $data['outcome'],
                'subject' => $data['subject'],
                'report_text' => $data['report_text'],
                'follow_up_at' => $data['follow_up_at'] ?? null,
            ]);
        });
    }

    public function addAppointmentNote(AppointmentUser $appointment, User $actor, string $note): AppointmentConsultationCase
    {
        return DB::transaction(function () use ($appointment, $actor, $note) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            $case = AppointmentConsultationCase::lockForUpdate()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['state' => AppointmentConsultationCase::STATE_OPEN]
            );

            if (filled($case->appointment_note)) {
                throw ValidationException::withMessages([
                    'appointment_note' => 'برای این نوبت قبلاً توضیحات ثبت شده است و امکان ثبت توضیح دوم وجود ندارد.',
                ]);
            }

            $case->update([
                'appointment_note' => $note,
                'note_author_id' => $actor->id,
                'note_author_role' => (int) $actor->id === (int) $appointment->doctor_id ? 'PRACTITIONER' : 'ADMIN',
                'note_created_at' => now(),
            ]);

            return $case->refresh();
        });
    }

    public function complete(AppointmentUser $appointment, User $actor): AppointmentConsultationCase
    {
        return DB::transaction(function () use ($appointment, $actor) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            $case = AppointmentConsultationCase::lockForUpdate()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['state' => AppointmentConsultationCase::STATE_OPEN]
            );
            if ($case->isClosed()) return $case;
            if ($appointment->status !== \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL) {
                throw ValidationException::withMessages(['completion' => 'اتمام مشاوره فقط برای نوبت پرداخت‌شده و لغونشده مجاز است.']);
            }
            if (! $case->reports()->exists()) {
                throw ValidationException::withMessages(['completion' => 'قبل از اتمام مشاوره باید حداقل یک گزارش ثبت شود.']);
            }
            $completedAt = now();
            $case->update([
                'state' => AppointmentConsultationCase::STATE_COMPLETED,
                'completed_at' => $completedAt,
                'completed_by' => $actor->id,
            ]);
            $case->events()->create([
                'actor_id' => $actor->id,
                'action' => 'COMPLETED',
                'snapshot' => ['completed_at' => $completedAt->toIso8601String(), 'reports_count' => $case->reports()->count()],
            ]);

            return $case->refresh();
        });
    }

    public function markPatientNoShow(AppointmentUser $appointment, User $actor): AppointmentConsultationCase
    {
        return DB::transaction(function () use ($appointment, $actor) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            abort_unless((int) $actor->id === (int) $appointment->doctor_id || $actor->can('SUPER_ADMIN'), 403);
            $case = $this->ensure($appointment);
            if ($case->state === AppointmentConsultationCase::STATE_PATIENT_NO_SHOW) return $case;
            if ($case->isClosed()) {
                throw ValidationException::withMessages(['no_show' => 'مشاوره قبلاً تمام شده و قابل تبدیل به عدم حضور نیست.']);
            }
            if ($appointment->status !== \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL
                || $appointment->kind !== \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP) {
                throw ValidationException::withMessages(['no_show' => 'عدم حضور بیمار فقط برای نوبت تلفنی پرداخت‌شده و لغونشده مجاز است.']);
            }
            if (! $appointment->date_visit || ! $appointment->start_time || ! $appointment->end_time) {
                throw ValidationException::withMessages(['no_show' => 'تاریخ یا ساعت شروع و پایان نوبت مشخص نیست؛ عدم حضور قابل ثبت نیست.']);
            }
            $start = \Carbon\Carbon::parse($appointment->date_visit, 'Asia/Tehran')->setTimeFromTimeString($appointment->start_time);
            $end = $start->copy()->setTimeFromTimeString($appointment->end_time);
            if ($end->lte($start)) $end->addDay();
            if (! now('Asia/Tehran')->gt($end)) {
                throw ValidationException::withMessages(['no_show' => 'هنوز زمان پایان نوبت نگذشته است. پایان نوبت: '.verta($end)->format('Y/m/d H:i:s')]);
            }
            // Only evidence from the reserved window can prevent a no-show. The PBX
            // state is a fallback for legacy rows that do not have an entered time.
            $occurredInWindow = function ($call) use ($start, $end): bool {
                if ($call->call_entered_at) {
                    return ! $call->call_entered_at->lt($start) && ! $call->call_entered_at->gt($end);
                }

                return $call->occurredDuringAppointment();
            };
            $calls = $appointment->callLogs()->get()->filter($occurredInWindow);
            // A PBX may have recorded the caller before it resolved an appointment ID.
            $phone = preg_replace('/\D+/', '', (string) $appointment->user?->mobile);
            if (strlen($phone) >= 10) {
                $phone = substr($phone, -10);
                $unlinked = \Modules\OnlineConsultation\Models\AppointmentCallLog::whereNull('appointment_id')
                    ->whereBetween('call_entered_at', [$start, $end])->get()
                    ->filter(fn ($call) => substr(preg_replace('/\D+/', '', $call->patient_phone), -10) === $phone);
                $calls = $calls->concat($unlinked)->unique('id');
            }
            $patientCalls = $calls->filter(fn ($call) => $call->direction !== 'OUTBOUND');
            $answered = $calls->filter(fn ($call) => $call->final_result === 'ANSWERED' || $call->answered_at || $call->talk_duration_seconds > 0);
            if ($patientCalls->isNotEmpty() || $answered->isNotEmpty()) {
                throw ValidationException::withMessages(['no_show' => sprintf(
                    'بیمار در بازه نوبت %d بار تماس گرفته است؛ %d تماس دارای پاسخ یا مکالمه و %d تلاش بیمار بدون پاسخ ثبت شده است. تماس‌های پیش از شروع نوبت محاسبه نمی‌شوند؛ اما هر تماس کوتاه یا ناموفق در بازه نوبت مانع ثبت عدم حضور است.',
                    $patientCalls->count(), $answered->count(), $patientCalls->filter(fn ($call) => $call->final_result !== 'ANSWERED' && ! $call->answered_at && ! $call->talk_duration_seconds)->count()
                )]);
            }
            $incidentCalls = \Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer::with('callLog')
                ->where('appointment_id', $appointment->id)->get()
                ->filter(function ($incident) use ($occurredInWindow, $start, $end) {
                    if ($incident->callLog) return $occurredInWindow($incident->callLog);
                    $occurredAt = $incident->ring_started_at ?: $incident->no_answer_at;
                    return $occurredAt && ! $occurredAt->lt($start) && ! $occurredAt->gt($end);
                })->pluck('call_id')
                ->merge(\Modules\OnlineConsultation\Models\AppointmentConsultantHangup::with('callLog')
                    ->where('appointment_id', $appointment->id)->get()
                    ->filter(function ($incident) use ($occurredInWindow, $start, $end) {
                        if ($incident->callLog) return $occurredInWindow($incident->callLog);
                        return ! $incident->hung_up_at->lt($start) && ! $incident->hung_up_at->gt($end);
                    })->pluck('call_id'))->unique();
            if ($incidentCalls->isNotEmpty()) {
                throw ValidationException::withMessages(['no_show' => 'در بازه این نوبت '.$incidentCalls->count().' تماس دارای رویداد عدم پاسخ یا قطع مشاور ثبت شده است؛ حتی بدون گزارش نهایی تماس، عدم حضور بیمار مجاز نیست.']);
            }
            $billingService = app(AppointmentBillingService::class);
            $billing = $billingService->ensure($appointment);
            if (! $billing || $billing->total_paid_amount <= 0 || $billing->payout_hourly_rate_snapshot <= 0) {
                throw ValidationException::withMessages(['no_show' => 'اطلاعات مالی، مبلغ پرداخت یا نرخ سهم مشاور ثبت نشده یا معتبر نیست.']);
            }
            $reason = 'عدم حضور بیمار؛ پایان بازه نوبت گذشته و در بازه رزروشده هیچ تلاش تماس بیمار یا مکالمه ثبت نشده است. تماس‌های پیش از شروع نوبت محاسبه نشدند؛ کل زمان رزروشده محاسبه شد و بازگشت کیف پول صفر است.';
            $billing = $billingService->settlePatientNoShow($billing, $actor, $reason);
            $case->update(['state' => AppointmentConsultationCase::STATE_PATIENT_NO_SHOW, 'completed_at' => now(), 'completed_by' => $actor->id]);
            $case->events()->create([
                'actor_id' => $actor->id, 'action' => 'PATIENT_NO_SHOW', 'reason' => $reason,
                'snapshot' => [
                    'appointment_start_at' => $start->toIso8601String(), 'appointment_end_at' => $end->toIso8601String(),
                    'checked_at' => now()->toIso8601String(), 'patient_calls_count' => 0,
                    'outbound_call_ids' => $calls->pluck('call_id')->all(), 'actor_name' => $actor->fullName,
                    'billing' => $billing->toArray(),
                ],
            ]);
            return $case->refresh();
        });
    }

    public function reopen(AppointmentUser $appointment, User $actor, string $reason): AppointmentConsultationCase
    {
        return DB::transaction(function () use ($appointment, $actor, $reason) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            $case = AppointmentConsultationCase::where('appointment_id', $appointment->id)->lockForUpdate()->firstOrFail();
            if ($case->state === AppointmentConsultationCase::STATE_PATIENT_NO_SHOW) {
                throw ValidationException::withMessages(['reopen_reason' => 'عدم حضور بیمار دارای تسویه قطعی است و با بازگشایی عادی قابل تغییر نیست.']);
            }
            if ($case->state === AppointmentConsultationCase::STATE_OPEN) return $case;
            $reopenedAt = now();
            $case->update([
                'state' => AppointmentConsultationCase::STATE_OPEN,
                'reopened_at' => $reopenedAt,
                'reopened_by' => $actor->id,
                'reopen_reason' => $reason,
            ]);
            $case->events()->create([
                'actor_id' => $actor->id,
                'action' => 'REOPENED',
                'reason' => $reason,
                'snapshot' => ['reopened_at' => $reopenedAt->toIso8601String(), 'previous_completed_at' => $case->completed_at?->toIso8601String()],
            ]);

            return $case->refresh();
        });
    }
}
