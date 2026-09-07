<?php

namespace Modules\OnlineConsultation\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;

class ConsultantDashboardService
{
    public const SHORT_CALL_SECONDS = 60;

    public function period(string $period, ?string $from = null, ?string $to = null): array
    {
        $today = now()->startOfDay();

        return match ($period) {
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => $this->monthPeriod($from),
            'custom' => [$this->jalali($from)?->startOfDay() ?? $today, $this->jalali($to)?->endOfDay() ?? now()->endOfDay()],
            default => [$today, now()->endOfDay()],
        };
    }

    public function monthPeriod(?string $month = null): array
    {
        $month = $month ?: verta()->format('Y/m');
        if (preg_match('/^(\d{4})\/(0[1-9]|1[0-2])$/', $month, $matches) !== 1) {
            $month = verta()->format('Y/m');
            preg_match('/^(\d{4})\/(\d{2})$/', $month, $matches);
        }
        $year = (int) $matches[1];
        $number = (int) $matches[2];
        $nextYear = $number === 12 ? $year + 1 : $year;
        $nextMonth = $number === 12 ? 1 : $number + 1;
        $from = \Verta::parse(sprintf('%04d/%02d/01', $year, $number))->toCarbon()->startOfDay();
        $to = \Verta::parse(sprintf('%04d/%02d/01', $nextYear, $nextMonth))->toCarbon()->startOfDay()->subSecond();

        return [$from, $to];
    }

    public function dayPeriod(?string $date = null): array
    {
        $day = $this->jalali($date) ?? now();

        return [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
    }

    public function monthNames(): array
    {
        return [1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند'];
    }

    public function monthOptions(int $count = 24): array
    {
        $current = verta();
        $year = (int) $current->format('Y');
        $month = (int) $current->format('m');
        $names = $this->monthNames();
        $options = [];
        for ($i = 0; $i < $count; $i++) {
            $value = sprintf('%04d/%02d', $year, $month);
            $options[$value] = $names[$month].' '.$year;
            if (--$month === 0) { $month = 12; $year--; }
        }

        return $options;
    }

    public function query(Carbon $from, Carbon $to, ?int $practitionerId = null): Builder
    {
        return AppointmentUser::query()
            ->whereIn('kind', [AppointmentUserKindEnum::ONLINE->value, AppointmentUserKindEnum::VOIP->value])
            ->whereBetween('date_visit', [$from, $to])
            ->whereHas('doctor', fn (Builder $q) => $q->whereHas('consultationPractitioner', fn (Builder $p) => $p->where('active', true)))
            ->when($practitionerId, fn (Builder $q) => $q->where('doctor_id', $practitionerId));
    }

    public function decorate(AppointmentUser $appointment): AppointmentUser
    {
        $calls = $appointment->callLogs;
        $answered = $calls->where('final_result', 'ANSWERED')->where('talk_duration_seconds', '>', 0);
        $inbound = $calls->where('direction', 'INBOUND');
        $outbound = $calls->where('direction', 'OUTBOUND');
        $past = $appointment->date_visit?->isPast() ?? false;
        $status = 'pending';
        $reason = null;

        if ($answered->isNotEmpty()) {
            $status = $answered->sum('talk_duration_seconds') < self::SHORT_CALL_SECONDS ? 'review' : ($past ? 'completed' : 'in_progress');
            $reason = $status === 'review' ? 'تماس برقرار شده اما مجموع مکالمه کمتر از یک دقیقه است.' : null;
        } elseif ($past && $inbound->isNotEmpty() && $inbound->every(fn ($c) => $c->final_result !== 'ANSWERED')) {
            $status = 'practitioner_no_answer';
            $reason = 'بیمار تماس گرفته اما مشاور پاسخ نداده است.';
        } elseif ($past && $outbound->isNotEmpty() && $outbound->every(fn ($c) => $c->final_result !== 'ANSWERED')) {
            $status = 'patient_no_answer';
            $reason = 'مشاور تماس گرفته اما بیمار پاسخ نداده است.';
        } elseif ($past && $calls->isNotEmpty()) {
            $status = 'failed';
            $reason = 'تمام تلاش‌های تماس ناموفق یا بدون مکالمه بوده‌اند.';
        } elseif ($past) {
            $status = 'missed';
            $reason = 'زمان نوبت گذشته و هیچ تماسی ثبت نشده است.';
        }

        $billing = $appointment->billingRecord;
        if ($billing?->refund_status === 'completed') {
            $financial = 'settled';
        } elseif ($billing && $billing->suggested_refund_amount > 0) {
            $financial = 'refundable';
        } else {
            $financial = 'unsettled';
        }

        $appointment->setAttribute('dashboard', [
            'status' => $status, 'reason' => $reason, 'alert' => $past && $answered->isEmpty(),
            'calls' => $calls->count(), 'answered' => $answered->count(), 'unanswered' => $calls->count() - $answered->count(),
            'patient_attempts' => $inbound->count(), 'practitioner_attempts' => $outbound->count(),
            'talk_seconds' => (int) $answered->sum('talk_duration_seconds'), 'last_call_at' => $calls->max('call_entered_at'),
            'reserved_minutes' => (int) ($billing?->reserved_minutes ?: $this->reservedMinutes($appointment)),
            'remaining_minutes' => (int) ($billing?->approved_unused_minutes ?? 0), 'financial' => $financial,
        ]);

        return $appointment;
    }

    public function stats(Collection $appointments): array
    {
        $items = $appointments->map(fn ($a) => $this->decorate($a));
        $talk = (int) $items->sum(fn ($a) => $a->dashboard['talk_seconds']);
        $calls = (int) $items->sum(fn ($a) => $a->dashboard['calls']);
        $answered = (int) $items->sum(fn ($a) => $a->dashboard['answered']);

        return [
            'total' => $items->count(), 'completed' => $items->whereIn('dashboard.status', ['completed'])->count(),
            'missed' => $items->where('dashboard.alert', true)->count(),
            'remaining' => $items->filter(fn ($a) => in_array($a->dashboard['status'], ['pending', 'in_progress'], true))->count(),
            'calls' => $calls, 'answered' => $answered, 'unanswered' => $calls - $answered,
            'patients' => $items->pluck('user_id')->filter()->unique()->count(), 'talk_seconds' => $talk,
            'average_seconds' => $answered ? (int) round($talk / $answered) : 0,
            'income' => (int) $items->sum(fn ($a) => $a->billingRecord?->total_paid_amount ?? 0),
            'refundable' => (int) $items->sum(fn ($a) => $a->billingRecord?->refund_status !== 'completed' ? ($a->billingRecord?->suggested_refund_amount ?? 0) : 0),
            'refunded' => (int) $items->sum(fn ($a) => ($a->billingRecord?->refunded_amount ?? 0) + ($a->billingRecord?->adjustments?->sum('amount_change') ?? 0)),
        ];
    }

    private function jalali(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }
        try {
            return \Verta::parse($value)->toCarbon();
        } catch (\Throwable) {
            return null;
        }
    }

    private function reservedMinutes(AppointmentUser $appointment): int
    {
        if (! $appointment->start_time || ! $appointment->end_time) {
            return 0;
        }
        $start = Carbon::parse($appointment->start_time);
        $end = Carbon::parse($appointment->end_time);
        if ($end->lte($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }
}
