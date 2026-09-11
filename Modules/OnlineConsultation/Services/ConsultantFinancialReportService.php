<?php

namespace Modules\OnlineConsultation\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

class ConsultantFinancialReportService
{
    public function __construct(
        private readonly ConsultantDashboardService $dashboard,
        private readonly AppointmentBillingService $billingService,
    ) {}

    public function period(string $period, ?string $month, ?string $from, ?string $to): array
    {
        return $this->dashboard->period($period, $period === 'month' ? $month : $from, $to);
    }

    public function report(Carbon $from, Carbon $to, ?int $profileId = null, string $settlementStatus = 'all'): array
    {
        $profiles = ConsultationPractitioner::with('user')
            ->when($profileId, fn ($query) => $query->whereKey($profileId))
            ->orderByDesc('active')->orderBy('display_name')->get();
        $userIds = $profiles->pluck('user_id');

        $appointments = AppointmentUser::query()
            ->whereIn('kind', [AppointmentUserKindEnum::ONLINE->value, AppointmentUserKindEnum::VOIP->value])
            ->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)
            ->whereBetween('date_visit', [$from, $to])
            ->whereIn('doctor_id', $userIds)
            ->with([
                'user', 'doctor', 'feedbacks', 'consultationCase',
                'callLogs.consultantHangup', 'callLogs.consultantNoAnswer',
                'billingRecord.adjustments',
            ])->get();

        // Keep financial snapshots aligned even when call logs were imported directly by the VoIP integration.
        $appointments->each(function (AppointmentUser $appointment) {
            $billingRecord = $appointment->billingRecord ?: $this->billingService->ensure($appointment);
            if ($billingRecord) {
                $refreshed = $this->billingService->refresh($billingRecord);
                $appointment->setRelation('billingRecord', $refreshed->load('adjustments'));
            }
        });

        $appointments = $appointments->filter(function (AppointmentUser $appointment) use ($settlementStatus) {
            $isFinalized = $appointment->billingRecord?->refund_status === 'completed';

            return match ($settlementStatus) {
                'finalized' => $isFinalized,
                'pending' => ! $isFinalized,
                default => true,
            };
        })->values();

        $rows = $profiles->map(function (ConsultationPractitioner $profile) use ($appointments) {
            $items = $appointments->where('doctor_id', $profile->user_id)->values();
            return ['profile' => $profile, 'metrics' => $this->metrics($items)];
        })->values();

        $allMetrics = $this->metrics($appointments);
        $appointmentRows = $appointments->sortByDesc('date_visit')->map(fn (AppointmentUser $appointment) => [
            'appointment' => $appointment,
            'metrics' => $this->metrics(collect([$appointment])),
        ])->values();
        $daily = $appointments->groupBy(fn ($appointment) => $appointment->date_visit?->toDateString())
            ->filter(fn ($items, $date) => filled($date))
            ->sortKeys()
            ->map(fn (Collection $items, string $date) => [
                'date' => $date,
                'label' => verta(Carbon::parse($date))->format('Y/m/d'),
                'metrics' => $this->metrics($items),
            ])->values();

        return compact('rows', 'allMetrics', 'daily', 'appointments', 'appointmentRows');
    }

    public function metrics(Collection $appointments): array
    {
        $decorated = $appointments->map(fn (AppointmentUser $appointment) => $this->dashboard->decorate($appointment));
        $calls = $appointments->flatMap(fn (AppointmentUser $appointment) => $appointment->callLogs)->values();
        $answered = $calls->filter(fn ($call) => $call->final_result === 'ANSWERED' && (int) $call->talk_duration_seconds > 0);
        $unanswered = $calls->filter(fn ($call) => $call->countsAsUnanswered());
        $reportableCallCount = $answered->count() + $unanswered->count();
        $diverted = $calls->where('connection_type', 'DIVERTED');
        $billing = $appointments->pluck('billingRecord')->filter();
        $settledBilling = $billing->where('refund_status', 'completed');
        $pendingBilling = $billing->where('refund_status', '!=', 'completed');
        $gross = (int) $settledBilling->sum('total_paid_amount');
        $pendingGross = (int) $pendingBilling->sum('total_paid_amount');
        $refunded = (int) $settledBilling->sum(fn ($record) => max(0, (int) $record->refunded_amount + (int) $record->adjustments->sum('amount_change')));
        $feedbacks = $appointments->flatMap(fn ($appointment) => $appointment->feedbacks->map(fn ($feedback) => [
            'score' => $this->feedbackScore($appointment, (int) $feedback->answer),
            'question' => (int) $feedback->question,
        ]));
        $surveyAppointments = $appointments->filter(fn ($appointment) => $appointment->feedbacks->isNotEmpty() || filled($appointment->surveyVoiceUrl()));
        $shortCallThresholdSeconds = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes) * 60;
        $hangups = $calls->filter(fn ($call) => $call->isConsultantHangupWarning($shortCallThresholdSeconds));
        $noAnswers = $calls->filter(fn ($call) => $call->consultantNoAnswer !== null && $call->occurredDuringAppointment());
        $resultCounts = $calls->groupBy(fn ($call) => $call->isEarlyCall() ? 'EARLY_CALL' : ($call->final_result ?: 'UNKNOWN'))->map->count()->sortDesc()->all();
        $rawTalkSeconds = (int) $billing->sum('raw_answered_talk_seconds');
        $ignoredTalkSeconds = (int) $billing->sum('ignored_talk_seconds');
        $talkSeconds = (int) $billing->sum('answered_talk_seconds');
        if ($billing->isEmpty()) {
            $rawTalkSeconds = (int) $answered->sum('talk_duration_seconds');
            $talkSeconds = $rawTalkSeconds;
        }
        $practitionerIncome = (int) $settledBilling->sum('practitioner_earned_amount');
        $platformProfit = (int) $settledBilling->sum('platform_profit_amount');
        $pendingRefund = (int) $pendingBilling->sum('suggested_refund_amount');
        $effectiveRefund = $refunded;

        return [
            'appointments' => $appointments->count(),
            'patient_no_show' => $decorated->where('dashboard.status', 'patient_no_show')->count(),
            'completed' => $decorated->where('dashboard.status', 'completed')->count(),
            'open' => $decorated->whereIn('dashboard.status', ['pending', 'in_progress', 'review'])->count(),
            'missed_appointments' => $decorated->where('dashboard.alert', true)->count(),
            'unique_patients' => $appointments->pluck('user_id')->filter()->unique()->count(),
            'reserved_minutes' => (int) $decorated->sum(fn ($appointment) => $appointment->dashboard['reserved_minutes']),
            'calls' => $calls->count(),
            'inbound' => $calls->where('direction', 'INBOUND')->count(),
            'outbound' => $calls->where('direction', 'OUTBOUND')->count(),
            'answered' => $answered->count(),
            'unanswered' => $unanswered->count(),
            'early_calls' => $calls->filter(fn ($call) => $call->isEarlyCall())->count(),
            'answer_rate' => $reportableCallCount ? round($answered->count() * 100 / $reportableCallCount, 1) : 0,
            'consultant_hangups' => $hangups->count(),
            'phone_hangups' => $hangups->filter(fn ($call) => $call->consultantHangup?->hangup_via === 'PHONE')->count(),
            'softphone_hangups' => $hangups->filter(fn ($call) => $call->consultantHangup?->hangup_via === 'SOFTPHONE')->count(),
            'consultant_no_answers' => $noAnswers->count(),
            'diverted' => $diverted->count(),
            'diverted_talk_seconds' => (int) $diverted->sum('talk_duration_seconds'),
            'diverted_total_seconds' => (int) $diverted->sum('total_duration_seconds'),
            'raw_talk_seconds' => $rawTalkSeconds,
            'ignored_talk_seconds' => $ignoredTalkSeconds,
            'talk_seconds' => $talkSeconds,
            'talk_minutes' => (int) ceil($talkSeconds / 60),
            'ring_seconds' => (int) $calls->sum('ring_duration_seconds'),
            'wait_seconds' => (int) $calls->sum('wait_duration_seconds'),
            'average_talk_seconds' => $answered->count() ? (int) round($talkSeconds / $answered->count()) : 0,
            'gross_income' => $gross,
            'pending_gross_income' => $pendingGross,
            'all_gross_income' => $gross + $pendingGross,
            'refunded' => $refunded,
            'pending_refund' => $pendingRefund,
            'effective_refund' => $effectiveRefund,
            'net_income' => max(0, $gross - $refunded),
            'net_after_refund' => $gross - $effectiveRefund,
            'practitioner_income' => $practitionerIncome,
            'platform_profit' => $platformProfit,
            'missing_payout_rate' => $billing->filter(fn ($record) => (int) $record->total_paid_amount > 0 && (int) $record->payout_hourly_rate_snapshot <= 0)->count(),
            'settled' => $billing->where('refund_status', 'completed')->count(),
            'unsettled' => $billing->where('refund_status', '!=', 'completed')->count(),
            'survey_appointments' => $surveyAppointments->count(),
            'survey_answers' => $feedbacks->count(),
            'survey_average' => $feedbacks->count() ? round($feedbacks->avg('score'), 1) : null,
            'voice_surveys' => $appointments->filter(fn ($appointment) => filled($appointment->surveyVoiceUrl()))->count(),
            'survey_distribution' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($score) => [$score => $feedbacks->where('score', $score)->count()])->all(),
            'call_results' => $resultCounts,
        ];
    }

    private function feedbackScore(AppointmentUser $appointment, int $answer): int
    {
        $voipScore = data_get($appointment->details, AppointmentUser::DETAIL_SURVEY.'.'.AppointmentUser::DETAIL_SURVEY);
        if (is_numeric($voipScore)) {
            return max(1, min(5, (int) $voipScore));
        }

        return [0 => 5, 1 => 4, 2 => 3, 3 => 1][$answer] ?? max(1, min(5, $answer));
    }
}
