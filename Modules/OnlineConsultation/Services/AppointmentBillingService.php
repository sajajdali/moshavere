<?php

namespace Modules\OnlineConsultation\Services;

use Modules\User\service\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Models\AppointmentBillingAdjustment;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\User\Entities\User;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class AppointmentBillingService
{
    public function ensure(AppointmentUser $appointment): ?AppointmentBillingRecord
    {
        if ($appointment->trashed()) return null;
        return DB::transaction(function () use ($appointment) {
            $appointment = AppointmentUser::lockForUpdate()->findOrFail($appointment->id);
            return $this->ensureLocked($appointment);
        });
    }

    private function ensureLocked(AppointmentUser $appointment): ?AppointmentBillingRecord
    {
        if ($appointment->status === AppointmentUserStatusEnum::STATUS_CANCEL) {
            return null;
        }

        if (! ConsultationAccess::schemaReady([
            'consultation_practitioners',
            'appointment_call_logs',
            'appointment_billing_records',
            'appointment_billing_audits',
        ])) {
            return null;
        }

        $profile = ConsultationPractitioner::where('user_id', $appointment->doctor_id)->first();
        if (! $profile || ! $appointment->user_id || ! $appointment->doctor_id) return null;

        $reserved = $this->reservedMinutes($appointment, $profile->duration_minutes);
        $rate = (int) ($profile->hourly_rate ?? $profile->fee ?? 0);
        $payoutRate = (int) ($profile->payout_hourly_rate ?? 0);
        $type = $this->type($appointment);
        $explicitPaid = (int) data_get($appointment->details, AppointmentUser::DETAIL_PAYMENT.'.'.AppointmentUser::DETAIL_PAYMENT_PRICE.'.int', 0);
        $paid = $explicitPaid;
        if ($paid === 0 && $type !== 'in_person') $paid = $this->amountForMinutes($rate, $reserved);

        $record = AppointmentBillingRecord::firstOrCreate(['appointment_id' => $appointment->id], [
            'patient_id' => $appointment->user_id, 'practitioner_id' => $appointment->doctor_id,
            'consultation_type' => $type, 'hourly_rate_snapshot' => $rate,
            'payout_hourly_rate_snapshot' => $payoutRate,
            'reserved_minutes' => $reserved, 'total_paid_amount' => $paid,
        ]);
        if (! $record->wasRecentlyCreated && $record->refund_status !== 'completed' && $explicitPaid > 0 && (int) $record->total_paid_amount !== $explicitPaid) {
            $record->total_paid_amount = $explicitPaid;
            $record->save();
        }
        $record = $this->refresh($record);
        if (! $record->audits()->exists()) {
            $this->audit($record, 'snapshot_created', null, $record->suggested_refund_amount, null);
        }
        return $record;
    }

    public function refresh(AppointmentBillingRecord $record): AppointmentBillingRecord
    {
        return DB::transaction(function () use ($record) {
            AppointmentUser::withTrashed()->lockForUpdate()->findOrFail($record->appointment_id);
            $locked = AppointmentBillingRecord::lockForUpdate()->findOrFail($record->id);
            $locked->wasRecentlyCreated = $record->wasRecentlyCreated;
            return $this->refreshLocked($locked);
        });
    }

    private function refreshLocked(AppointmentBillingRecord $record): AppointmentBillingRecord
    {
        $appointment = $record->appointment;
        if (! $appointment) {
            return $record;
        }
        if ($appointment->status === AppointmentUserStatusEnum::STATUS_CANCEL) {
            return $record;
        }
        if ((int) $record->payout_hourly_rate_snapshot <= 0) {
            $currentPayoutRate = (int) ConsultationPractitioner::where('user_id', $record->practitioner_id)->value('payout_hourly_rate');
            if ($currentPayoutRate > 0) {
                $record->payout_hourly_rate_snapshot = $currentPayoutRate;
            }
        }
        $wasCreated = $record->wasRecentlyCreated;
        $previousTalk = (int) $record->answered_talk_seconds;
        $previousIgnoredTalk = (int) $record->ignored_talk_seconds;
        $previousUnused = (int) $record->system_unused_minutes;
        $answeredCalls = $appointment->callLogs()
            ->where('final_result', 'ANSWERED')
            ->where('talk_duration_seconds', '>', 0)
            ->get(['talk_duration_seconds']);
        $rawTalk = (int) $answeredCalls->sum('talk_duration_seconds');
        $thresholdSeconds = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes) * 60;
        $ignoredTalk = $thresholdSeconds > 0
            ? (int) $answeredCalls->where('talk_duration_seconds', '<=', $thresholdSeconds)->sum('talk_duration_seconds')
            : 0;
        $talk = max(0, $rawTalk - $ignoredTalk);
        $usedMinutes = min($record->reserved_minutes, (int) ceil($talk / 60));
        $unused = max(0, $record->reserved_minutes - $usedMinutes);
        $isCompleted = $record->refund_status === 'completed';
        $approved = ($isCompleted || $record->approved_by || filled($record->adjustment_reason))
            ? min((int) $record->approved_unused_minutes, (int) $record->reserved_minutes)
            : $unused;
        $suggestedRefund = min((int) $record->total_paid_amount, $this->amountForMinutes($record->hourly_rate_snapshot, $approved));
        $effectiveRefund = $isCompleted
            ? max(0, (int) $record->refunded_amount + (int) $record->adjustments()->sum('amount_change'))
            : $suggestedRefund;
        $correctedUnusedMinutes = $isCompleted
            ? $record->adjustments()->latest('id')->value('corrected_unused_minutes')
            : null;
        $settledUnusedMinutes = min(
            (int) $record->reserved_minutes,
            max(0, (int) ($correctedUnusedMinutes ?? $approved))
        );
        $settledUsedMinutes = $record->consultation_type === 'in_person'
            ? $usedMinutes
            : max(0, (int) $record->reserved_minutes - $settledUnusedMinutes);
        $netAfterRefund = max(0, (int) $record->total_paid_amount - $effectiveRefund);
        $practitionerEarned = min(
            $netAfterRefund,
            $this->amountForMinutes((int) $record->payout_hourly_rate_snapshot, $settledUsedMinutes)
        );

        $values = [
            'raw_answered_talk_seconds' => $rawTalk,
            'ignored_talk_seconds' => $ignoredTalk,
            'answered_talk_seconds' => $talk,
            'system_unused_minutes' => $unused,
            'used_amount' => $record->consultation_type === 'in_person'
                ? min((int) $record->total_paid_amount, $this->amountForMinutes($record->hourly_rate_snapshot, $usedMinutes))
                : $netAfterRefund,
            'practitioner_earned_amount' => $practitionerEarned,
            'platform_profit_amount' => $netAfterRefund - $practitionerEarned,
        ];
        if (! $isCompleted) {
            $values['approved_unused_minutes'] = $approved;
            $values['suggested_refund_amount'] = $suggestedRefund;
        }
        $record->forceFill($values);
        if ($record->isDirty()) $record->save();
        $record = $record->refresh();
        if ($wasCreated || $previousTalk !== $talk || $previousIgnoredTalk !== $ignoredTalk || $previousUnused !== $unused) {
            $this->audit($record, $wasCreated ? 'snapshot_created' : 'usage_recalculated', null, $record->suggested_refund_amount, null);
        }
        return $record;
    }

    public function approveMinutes(AppointmentBillingRecord $record, int $minutes, User $actor, ?string $reason): AppointmentBillingRecord
    {
        return DB::transaction(function () use ($record, $minutes, $actor, $reason) {
            AppointmentUser::lockForUpdate()->findOrFail($record->appointment_id);
            $record = AppointmentBillingRecord::lockForUpdate()->findOrFail($record->id);
            $this->ensureAppointmentIsNotCancelled($record);
            if ($record->refund_status === 'completed') throw ValidationException::withMessages(['approved_unused_minutes' => 'بازگشت وجه انجام شده و این رکورد دیگر قابل تغییر نیست.']);
            if ($minutes > $record->reserved_minutes) throw ValidationException::withMessages(['approved_unused_minutes' => 'زمان تأییدشده نمی‌تواند بیشتر از زمان رزروشده باشد.']);
            if ($minutes !== $record->system_unused_minutes && blank($reason)) throw ValidationException::withMessages(['reason' => 'برای اصلاح دستی زمان، ثبت دلیل الزامی است.']);
            $amount = min((int) $record->total_paid_amount, $this->amountForMinutes($record->hourly_rate_snapshot, $minutes));
            $record->update(['approved_unused_minutes' => $minutes, 'suggested_refund_amount' => $amount, 'refund_status' => 'approved', 'approved_by' => $actor->id, 'approved_at' => now(), 'adjustment_reason' => $reason]);
            $this->audit($record, 'minutes_approved', $actor, $amount, $reason);
            return $this->refresh($record->refresh());
        });
    }

    public function refund(AppointmentBillingRecord $record, User $actor): AppointmentBillingRecord
    {
        return DB::transaction(function () use ($record, $actor) {
            AppointmentUser::lockForUpdate()->findOrFail($record->appointment_id);
            $record = AppointmentBillingRecord::lockForUpdate()->with('patient')->findOrFail($record->id);
            $this->ensureAppointmentIsNotCancelled($record);
            if ($record->refund_status === 'completed') return $record;
            if ($record->consultation_type === 'in_person') throw ValidationException::withMessages(['refund' => 'مشاوره حضوری پس از جلسه تسویه می‌شود و بازگشت زمان استفاده‌نشده ندارد.']);
            if ($record->appointment->status !== AppointmentUserStatusEnum::STATUS_SUCCESSFUL || $record->total_paid_amount <= 0) {
                throw ValidationException::withMessages(['refund' => 'تا پیش از تأیید پرداخت نوبت، بازگشت وجه امکان‌پذیر نیست.']);
            }
            $amount = min((int) $record->total_paid_amount, $this->amountForMinutes($record->hourly_rate_snapshot, $record->approved_unused_minutes));
            if ($amount <= 0) throw ValidationException::withMessages(['refund' => 'مبلغ قابل بازگشت صفر است.']);
            $wallet = app(WalletService::class)->refund($record->patient, $amount, 'appointment-consultation-refund:'.$record->id, [
                'appointment_id' => $record->appointment_id, 'billing_record_id' => $record->id,
                'approved_unused_minutes' => $record->approved_unused_minutes, 'approved_by' => $actor->id,
            ]);
            $record->update(['refunded_amount' => $amount, 'refund_status' => 'completed', 'wallet_transaction_id' => $wallet->id, 'approved_by' => $actor->id, 'approved_at' => now()]);
            $this->audit($record, 'wallet_refunded', $actor, $amount, $record->adjustment_reason);
            return $this->refresh($record->refresh());
        });
    }

    /**
     * Finalize the calculation and credit the patient's wallet as one atomic operation.
     * Repeated submissions are idempotent and can never create a second wallet credit.
     */
    public function confirmAndRefund(AppointmentBillingRecord $record, int $minutes, User $actor, ?string $reason): AppointmentBillingRecord
    {
        return DB::transaction(function () use ($record, $minutes, $actor, $reason) {
            AppointmentUser::lockForUpdate()->findOrFail($record->appointment_id);
            $record = AppointmentBillingRecord::lockForUpdate()->findOrFail($record->id);
            $this->ensureAppointmentIsNotCancelled($record);
            if ($record->refund_status === 'completed') {
                return $record;
            }
            if ($record->consultation_type === 'in_person') {
                throw ValidationException::withMessages(['refund' => 'برای نوبت حضوری عملیات بازگشت کیف پول انجام نمی‌شود.']);
            }
            if ($record->appointment->status !== AppointmentUserStatusEnum::STATUS_SUCCESSFUL || $record->total_paid_amount <= 0) {
                throw ValidationException::withMessages(['refund' => 'تا پیش از تأیید پرداخت نوبت، تسویه کیف پول امکان‌پذیر نیست.']);
            }

            $record = $this->refresh($record);
            if ($minutes > $record->reserved_minutes) {
                throw ValidationException::withMessages(['approved_unused_minutes' => 'زمان تأییدشده نمی‌تواند بیشتر از زمان رزروشده باشد.']);
            }
            if ($record->refund_status === 'approved' && $minutes !== (int) $record->approved_unused_minutes) {
                throw ValidationException::withMessages(['approved_unused_minutes' => 'این محاسبه قبلاً تأیید و قفل شده است و زمان آن قابل ویرایش نیست.']);
            }
            if ($minutes !== (int) $record->system_unused_minutes && blank($reason)) {
                throw ValidationException::withMessages(['reason' => 'برای تغییر زمان محاسبه‌شده توسط سیستم، ثبت دلیل الزامی است.']);
            }

            $minuteDifference = $minutes - (int) $record->system_unused_minutes;
            $isPractitionerAdjustment = $minuteDifference !== 0 && (int) $actor->id === (int) $record->practitioner_id;
            $confirmationAction = $minuteDifference === 0
                ? 'refund_confirmed'
                : ($isPractitionerAdjustment ? 'practitioner_adjusted_refund' : 'admin_adjusted_refund');
            $amount = min((int) $record->total_paid_amount, $this->amountForMinutes((int) $record->hourly_rate_snapshot, $minutes));
            $confirmedAt = now();
            $wallet = $amount > 0 ? app(WalletService::class)->refund(
                $record->patient,
                $amount,
                'appointment-consultation-refund:'.$record->id,
                [
                    'source' => 'online_consultation_final_confirmation',
                    'appointment_id' => $record->appointment_id,
                    'appointment_tracking_code' => $record->appointment->tracking_code,
                    'billing_record_id' => $record->id,
                    'patient_id' => $record->patient_id,
                    'practitioner_id' => $record->practitioner_id,
                    'approved_unused_minutes' => $minutes,
                    'system_unused_minutes' => (int) $record->system_unused_minutes,
                    'minute_difference' => $minuteDifference,
                    'minutes_changed_by_practitioner' => $isPractitionerAdjustment,
                    'hourly_rate_snapshot' => (int) $record->hourly_rate_snapshot,
                    'appointment_total_amount' => (int) $record->total_paid_amount,
                    'refund_amount' => $amount,
                    'practitioner_receivable' => min(
                        max(0, (int) $record->total_paid_amount - $amount),
                        $this->amountForMinutes(
                            (int) $record->payout_hourly_rate_snapshot,
                            max(0, (int) $record->reserved_minutes - $minutes)
                        )
                    ),
                    'confirmed_by' => $actor->id,
                    'confirmed_by_name' => $actor->fullName,
                    'confirmed_at' => $confirmedAt->toIso8601String(),
                    'reason' => $reason,
                ]
            ) : null;

            $record->update([
                'approved_unused_minutes' => $minutes,
                'suggested_refund_amount' => $amount,
                'refunded_amount' => $amount,
                'refund_status' => 'completed',
                'wallet_transaction_id' => $wallet?->id,
                'approved_by' => $actor->id,
                'approved_at' => $confirmedAt,
                'adjustment_reason' => $reason,
            ]);
            $auditReason = $minuteDifference === 0
                ? $reason
                : sprintf(
                    '%s زمان پیشنهادی سیستم را %d دقیقه %s کرد.%s',
                    $isPractitionerAdjustment ? 'پزشک' : 'مدیر',
                    abs($minuteDifference),
                    $minuteDifference > 0 ? 'افزایش' : 'کاهش',
                    filled($reason) ? ' دلیل: '.$reason : ''
                );
            $record = $this->refresh($record->refresh());
            $this->audit($record, $confirmationAction, $actor, $amount, $auditReason);

            return $record;
        });
    }

    // Called inside the case transaction after locking the appointment and validating attendance.
    public function settlePatientNoShow(AppointmentBillingRecord $record, User $actor, string $reason): AppointmentBillingRecord
    {
        $record = AppointmentBillingRecord::lockForUpdate()->findOrFail($record->id);
        if ($record->refund_status === 'completed' || $record->refunded_amount > 0 || $record->adjustments()->exists()) {
            throw ValidationException::withMessages(['no_show' => 'این نوبت قبلاً تسویه یا اصلاح مالی شده است؛ ثبت عدم حضور مجاز نیست.']);
        }
        $record->update([
            'approved_unused_minutes' => 0, 'suggested_refund_amount' => 0, 'refunded_amount' => 0,
            'refund_status' => 'completed', 'approved_by' => $actor->id, 'approved_at' => now(),
            'adjustment_reason' => $reason,
        ]);
        $record = $this->refresh($record->refresh());
        $this->audit($record, 'patient_no_show_settled', $actor, (int) $record->total_paid_amount, $reason);
        return $record;
    }

    public function amountForMinutes(int $hourlyRate, int $minutes): int
    {
        // Monetary calculations are rounded to the nearest 1,000 tomans.
        return intdiv(($hourlyRate * $minutes) + 30000, 60000) * 1000;
    }

    public function correctCompletedRefund(AppointmentBillingRecord $record, int $minutes, User $actor, string $reason, string $requestToken): AppointmentBillingAdjustment
    {
        return DB::transaction(function () use ($record, $minutes, $actor, $reason, $requestToken) {
            $existing = AppointmentBillingAdjustment::where('request_token', $requestToken)->first();
            if ($existing) return $existing;
            AppointmentUser::lockForUpdate()->findOrFail($record->appointment_id);
            $record = AppointmentBillingRecord::lockForUpdate()->with('patient')->findOrFail($record->id);
            $this->ensureAppointmentIsNotCancelled($record);
            if ($record->refund_status !== 'completed') throw ValidationException::withMessages(['correction' => 'اصلاح مالی فقط پس از انجام بازگشت وجه قابل ثبت است.']);
            if ($minutes > $record->reserved_minutes) throw ValidationException::withMessages(['corrected_unused_minutes' => 'زمان اصلاح‌شده بیشتر از زمان رزروشده است.']);
            $currentMinutes = (int) ($record->adjustments()->latest('id')->value('corrected_unused_minutes') ?? $record->approved_unused_minutes);
            $currentAmount = (int) $record->refunded_amount + (int) $record->adjustments()->sum('amount_change');
            $targetAmount = min((int) $record->total_paid_amount, $this->amountForMinutes($record->hourly_rate_snapshot, $minutes));
            $change = $targetAmount - $currentAmount;
            if ($change === 0) throw ValidationException::withMessages(['corrected_unused_minutes' => 'این مقدار تغییری در مبلغ ایجاد نمی‌کند.']);
            $key = 'appointment-billing-adjustment:'.$requestToken;
            $detail = [
                'source' => 'online_consultation_super_admin_correction',
                'appointment_id' => $record->appointment_id,
                'appointment_tracking_code' => $record->appointment->tracking_code,
                'billing_record_id' => $record->id,
                'patient_id' => $record->patient_id,
                'practitioner_id' => $record->practitioner_id,
                'previous_minutes' => $currentMinutes,
                'corrected_minutes' => $minutes,
                'previous_refund_amount' => $currentAmount,
                'corrected_refund_amount' => $targetAmount,
                'amount_change' => $change,
                'actor_id' => $actor->id,
                'actor_name' => $actor->fullName,
                'corrected_at' => now()->toIso8601String(),
                'reason' => $reason,
            ];
            $wallet = $change > 0
                ? app(WalletService::class)->credit($record->patient, $change, 'admin_adjustment', $key, $detail)
                : app(WalletService::class)->debit($record->patient, abs($change), 'admin_adjustment', $key, $detail);
            $adjustment = $record->adjustments()->create(['request_token' => $requestToken, 'previous_unused_minutes' => $currentMinutes, 'corrected_unused_minutes' => $minutes, 'amount_change' => $change, 'wallet_transaction_id' => $wallet->id, 'actor_id' => $actor->id, 'reason' => $reason]);
            $this->refresh($record->refresh());
            $this->audit($record, 'refund_corrected', $actor, abs($change), $reason);
            return $adjustment;
        });
    }

    private function reservedMinutes(AppointmentUser $appointment, ?int $fallback): int
    {
        if ($appointment->start_time && $appointment->end_time) {
            $start = \Carbon\Carbon::parse($appointment->start_time);
            $end = \Carbon\Carbon::parse($appointment->end_time);
            if ($end->lte($start)) $end->addDay();
            return max(1, $start->diffInMinutes($end));
        }
        return max(1, (int) ($fallback ?: 30));
    }

    private function type(AppointmentUser $appointment): string
    {
        return match ($appointment->kind) {
            AppointmentUserKindEnum::ONLINE => 'online', AppointmentUserKindEnum::VOIP => 'phone', default => 'in_person',
        };
    }

    private function ensureAppointmentIsNotCancelled(AppointmentBillingRecord $record): void
    {
        if ($record->appointment?->consultationCase?->state === 'PATIENT_NO_SHOW') {
            throw ValidationException::withMessages(['refund' => 'این نوبت با علت عدم حضور بیمار تسویه قطعی شده است؛ بازگشت وجه یا تغییر محاسبه مجاز نیست.']);
        }
        if ($record->appointment?->status === AppointmentUserStatusEnum::STATUS_CANCEL) {
            throw ValidationException::withMessages([
                'refund' => 'این نوبت لغو شده است؛ محاسبه، تأیید یا ثبت بازگشت وجه برای آن مجاز نیست.',
            ]);
        }
    }

    private function audit(AppointmentBillingRecord $record, string $action, ?User $actor, int $amount, ?string $reason): void
    {
        $record->audits()->create(['action' => $action, 'actor_id' => $actor?->id, 'system_unused_minutes' => $record->system_unused_minutes, 'approved_unused_minutes' => $record->approved_unused_minutes, 'amount' => $amount, 'reason' => $reason, 'snapshot' => $record->fresh()->toArray()]);
    }
}
