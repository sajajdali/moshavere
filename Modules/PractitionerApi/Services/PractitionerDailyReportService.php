<?php

namespace Modules\PractitionerApi\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Modules\OnlineConsultation\Services\ConsultantFinancialReportService;

class PractitionerDailyReportService
{
    public function __construct(
        private readonly ConsultantFinancialReportService $reports,
        private readonly ConsultantDashboardService $dashboard,
    ) {}

    public function report(ConsultationPractitioner $practitioner, array $filters): array
    {
        $timezone = (string) (ConsultationSetting::current()->timezone ?: 'Asia/Tehran');
        [$scope, $from, $to] = $this->period($filters, $timezone);
        $appointments = $this->dashboard->query(
            $from->copy()->setTimezone(config('app.timezone')),
            $to->copy()->setTimezone(config('app.timezone')),
            $practitioner->user_id
        )->with([
            'feedbacks', 'consultationCase', 'callLogs.consultantHangup',
            'callLogs.consultantNoAnswer', 'billingRecord.adjustments',
        ])->get();
        $allMetrics = $this->reports->metrics($appointments);
        $dailyByDate = $appointments->groupBy(fn ($appointment) => $appointment->date_visit?->toDateString())
            ->map(fn ($items) => ['metrics' => $this->reports->metrics($items->values())]);
        $days = collect(CarbonPeriod::create($from->toDateString(), $to->toDateString()))
            ->map(function (Carbon $day) use ($dailyByDate): array {
                $metrics = $dailyByDate->get($day->toDateString())['metrics'] ?? $this->emptyMetrics();
                return ['date' => $day->toDateString(), 'metrics' => $this->select($metrics)];
            })->values()->all();

        return [
            'scope' => $scope, 'timezone' => $timezone,
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'currency' => 'TOMAN', 'totals' => $this->select($allMetrics), 'days' => $days,
        ];
    }

    private function period(array $filters, string $timezone): array
    {
        $scope = $filters['scope'] ?? 'today';
        $today = now($timezone)->startOfDay();
        if ($scope === 'date') {
            $day = Carbon::createFromFormat('Y-m-d', $filters['date'], $timezone)->startOfDay();
            return [$scope, $day, $day->copy()->endOfDay()];
        }
        if ($scope === 'last_7_days') return [$scope, $today->copy()->subDays(6), $today->copy()->endOfDay()];
        return ['today', $today, $today->copy()->endOfDay()];
    }

    private function select(array $metrics): array
    {
        return [
            'appointments' => (int) $metrics['appointments'], 'unique_patients' => (int) $metrics['unique_patients'],
            'completed' => (int) $metrics['completed'], 'patient_no_show' => (int) $metrics['patient_no_show'],
            'open' => (int) $metrics['open'], 'missed_appointments' => (int) $metrics['missed_appointments'],
            'reserved_minutes' => (int) $metrics['reserved_minutes'], 'calls' => (int) $metrics['calls'],
            'inbound' => (int) $metrics['inbound'], 'outbound' => (int) $metrics['outbound'],
            'answered' => (int) $metrics['answered'], 'unanswered' => (int) $metrics['unanswered'],
            'early_calls' => (int) $metrics['early_calls'], 'call_results' => (array) $metrics['call_results'],
            'raw_talk_seconds' => (int) $metrics['raw_talk_seconds'], 'ignored_talk_seconds' => (int) $metrics['ignored_talk_seconds'],
            'billable_talk_seconds' => (int) $metrics['talk_seconds'], 'talk_minutes' => (int) $metrics['talk_minutes'],
            'settled' => (int) $metrics['settled'], 'unsettled' => (int) $metrics['unsettled'],
            'finalized_gross_amount' => (int) $metrics['gross_income'],
            'pending_gross_amount' => (int) $metrics['pending_gross_income'],
            'refunded_amount' => (int) $metrics['refunded'], 'pending_refund_amount' => (int) $metrics['pending_refund'],
            'net_amount' => (int) $metrics['net_after_refund'],
            'practitioner_income_amount' => (int) $metrics['practitioner_income'],
            'platform_profit_amount' => (int) $metrics['platform_profit'],
        ];
    }

    private function emptyMetrics(): array
    {
        return array_fill_keys([
            'appointments', 'unique_patients', 'completed', 'patient_no_show', 'open', 'missed_appointments',
            'reserved_minutes', 'calls', 'inbound', 'outbound', 'answered', 'unanswered', 'early_calls',
            'raw_talk_seconds', 'ignored_talk_seconds', 'talk_seconds', 'talk_minutes', 'settled', 'unsettled',
            'gross_income', 'pending_gross_income', 'refunded', 'pending_refund', 'net_after_refund',
            'practitioner_income', 'platform_profit',
        ], 0) + ['call_results' => []];
    }
}
