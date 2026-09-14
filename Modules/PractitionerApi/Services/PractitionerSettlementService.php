<?php

namespace Modules\PractitionerApi\Services;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerSettlementService
{
    public function __construct(
        private readonly AppointmentBillingService $billing,
        private readonly PractitionerAppointmentService $appointments,
    ) {}

    public function show(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $record = $this->billing->ensure($appointment);
        if (! $record) throw ValidationException::withMessages(['settlement' => ['اطلاعات مالی این نوبت قابل محاسبه نیست.']]);

        return $this->present($record->load(['approver', 'walletTransaction', 'adjustments', 'audits.actor']), $appointment);
    }

    public function confirm(ConsultationPractitioner $practitioner, int $appointmentId, array $data): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $this->ensureEnded($appointment);
        $record = $this->billing->ensure($appointment);
        if (! $record) throw ValidationException::withMessages(['settlement' => ['اطلاعات مالی این نوبت قابل محاسبه نیست.']]);
        if ((int) $data['approved_unused_minutes'] > (int) $record->reserved_minutes) {
            throw ValidationException::withMessages(['approved_unused_minutes' => ['زمان تأییدشده نمی‌تواند بیشتر از زمان رزروشده باشد.']]);
        }
        $wasFinalized = $record->refund_status === 'completed';
        $record = $this->billing->confirmAndRefund(
            $record, (int) $data['approved_unused_minutes'], $practitioner->user, $data['reason'] ?? null
        );

        return [
            'idempotent' => $wasFinalized,
            'settlement' => $this->present($record->load(['approver', 'walletTransaction', 'adjustments', 'audits.actor']), $appointment),
            'appointment' => $this->appointments->detail($practitioner, $appointmentId),
        ];
    }

    private function present(AppointmentBillingRecord $record, AppointmentUser $appointment): array
    {
        $effectiveRefund = $record->refund_status === 'completed'
            ? max(0, (int) $record->refunded_amount + (int) $record->adjustments->sum('amount_change'))
            : (int) $record->suggested_refund_amount;
        return [
            'id' => (int) $record->id, 'appointment_id' => (int) $record->appointment_id,
            'status' => $record->refund_status, 'finalized' => $record->refund_status === 'completed',
            'can_confirm' => $record->refund_status !== 'completed' && $this->hasEnded($appointment),
            'reserved_minutes' => (int) $record->reserved_minutes,
            'raw_talk_seconds' => (int) $record->raw_answered_talk_seconds,
            'ignored_talk_seconds' => (int) $record->ignored_talk_seconds,
            'connection_overhead_minutes' => (int) ($record->connection_overhead_minutes_snapshot ?? 0),
            'billable_talk_seconds' => (int) $record->billable_talk_seconds,
            'system_unused_minutes' => (int) $record->system_unused_minutes,
            'approved_unused_minutes' => (int) $record->approved_unused_minutes,
            'manual_adjustment_requires_reason' => true,
            'total_paid_amount' => (int) $record->total_paid_amount,
            'suggested_refund_amount' => (int) $record->suggested_refund_amount,
            'effective_refund_amount' => $effectiveRefund,
            'used_amount' => (int) $record->used_amount,
            'practitioner_earned_amount' => (int) $record->practitioner_earned_amount,
            'platform_profit_amount' => (int) $record->platform_profit_amount,
            'currency' => 'TOMAN', 'approved_at' => $record->approved_at?->toIso8601String(),
            'wallet_transaction_id' => $record->wallet_transaction_id ? (int) $record->wallet_transaction_id : null,
            'audit_count' => $record->audits->count(),
        ];
    }

    private function ownedAppointment(ConsultationPractitioner $practitioner, int $id): AppointmentUser
    {
        $appointment = AppointmentUser::query()->whereKey($id)->where('doctor_id', $practitioner->user_id)->first();
        if (! $appointment) throw new NotFoundHttpException('نوبت برای این پزشک یافت نشد.');
        return $appointment;
    }

    private function ensureEnded(AppointmentUser $appointment): void
    {
        if (! $this->hasEnded($appointment)) {
            throw ValidationException::withMessages(['settlement' => ['تسویه فقط پس از پایان کامل بازه نوبت مجاز است.']]);
        }
    }

    private function hasEnded(AppointmentUser $appointment): bool
    {
        if (! $appointment->date_visit || ! $appointment->start_time || ! $appointment->end_time) return false;
        $start = Carbon::parse($appointment->date_visit, 'Asia/Tehran')->setTimeFromTimeString($appointment->start_time);
        $end = $start->copy()->setTimeFromTimeString($appointment->end_time);
        if ($end->lte($start)) $end->addDay();
        return now('Asia/Tehran')->gt($end);
    }
}
