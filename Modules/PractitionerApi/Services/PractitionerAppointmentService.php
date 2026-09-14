<?php

namespace Modules\PractitionerApi\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerAppointmentService
{
    public function __construct(
        private readonly ConsultantDashboardService $consultations,
        private readonly PractitionerDashboardService $dashboard,
        private readonly PractitionerSoftphoneService $softphones,
    ) {}

    /** @return array{items: list<array<string, mixed>>, paginator: LengthAwarePaginator} */
    public function paginate(ConsultationPractitioner $practitioner, array $filters): array
    {
        [$timezone, $now] = $this->clock();
        $query = $this->query($practitioner);
        $this->applyPeriod($query, $filters, $timezone, $now);
        $this->applySearch($query, $filters['q'] ?? null);
        $paginator = $query->orderByDesc('date_visit')->paginate((int) ($filters['per_page'] ?? 20));
        $items = $paginator->getCollection()->map(function (AppointmentUser $appointment) use ($timezone, $now): array {
            $this->consultations->decorate($appointment);
            return $this->dashboard->presentAppointment($appointment, $timezone, $now) + [
                'settlement_status' => $appointment->dashboard['financial'],
                'paid' => (bool) ($appointment->billingRecord && $appointment->billingRecord->total_paid_amount > 0),
                'reports_count' => (int) $appointment->consultation_reports_count,
            ];
        })->values()->all();

        return compact('items', 'paginator');
    }

    /** @return array<string, mixed> */
    public function detail(ConsultationPractitioner $practitioner, int $id): array
    {
        [$timezone, $now] = $this->clock();
        $appointment = $this->query($practitioner)->whereKey($id)->first();
        if (! $appointment) throw new NotFoundHttpException('نوبت برای این پزشک یافت نشد.');
        $this->consultations->decorate($appointment);
        $item = $this->dashboard->presentAppointment($appointment, $timezone, $now);
        $billing = $appointment->billingRecord;

        return $item + [
            'server_time' => $now->toIso8601String(), 'timezone' => $timezone,
            'call_stats' => [
                'total' => (int) $appointment->dashboard['calls'], 'answered' => (int) $appointment->dashboard['answered'],
                'unanswered' => (int) $appointment->dashboard['unanswered'], 'early' => (int) $appointment->dashboard['early_calls'],
                'talk_seconds' => (int) $appointment->dashboard['talk_seconds'],
            ],
            'reports_count' => (int) $appointment->consultation_reports_count,
            'settlement' => [
                'status' => (string) $appointment->dashboard['financial'],
                'reserved_minutes' => (int) $appointment->dashboard['reserved_minutes'],
                'talk_seconds' => (int) $appointment->dashboard['talk_seconds'],
                'total_paid_amount' => (int) ($billing?->total_paid_amount ?? 0),
                'suggested_refund_amount' => (int) ($billing?->suggested_refund_amount ?? 0),
                'refunded_amount' => (int) ($billing?->refunded_amount ?? 0),
                'practitioner_earned_amount' => (int) ($billing?->practitioner_earned_amount ?? 0),
                'currency' => 'TOMAN',
            ],
            'actions' => $this->actions($appointment, $item, $now, $practitioner),
        ];
    }

    private function query(ConsultationPractitioner $practitioner): Builder
    {
        return $this->consultations->query(Carbon::create(2000, 1, 1), Carbon::create(2100, 1, 1), $practitioner->user_id)
            ->with(['user', 'service:id,title', 'place:id,title', 'callLogs', 'billingRecord.adjustments', 'consultationCase'])
            ->withCount('consultationReports');
    }

    private function applyPeriod(Builder $query, array $filters, string $timezone, Carbon $now): void
    {
        if (! empty($filters['date'])) {
            $day = Carbon::createFromFormat('Y-m-d', $filters['date'], $timezone);
            $query->whereBetween('date_visit', [$day->copy()->startOfDay()->setTimezone(config('app.timezone')), $day->copy()->endOfDay()->setTimezone(config('app.timezone'))]);
            return;
        }
        $today = $now->copy()->startOfDay();
        match ($filters['scope'] ?? 'today') {
            'tomorrow' => $query->whereBetween('date_visit', [$today->copy()->addDay()->setTimezone(config('app.timezone')), $today->copy()->addDay()->endOfDay()->setTimezone(config('app.timezone'))]),
            'past' => $query->where('date_visit', '<', $today->copy()->setTimezone(config('app.timezone'))),
            'all' => null,
            default => $query->whereBetween('date_visit', [$today->copy()->setTimezone(config('app.timezone')), $today->copy()->endOfDay()->setTimezone(config('app.timezone'))]),
        };
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! filled($search)) return;
        $value = trim((string) $search);
        $mobile = preg_replace('/[\s\-()]+/', '', $value);
        $query->whereHas('user', function (Builder $user) use ($value, $mobile): void {
            $user->where('mobile', 'like', '%'.$mobile.'%')
                ->orWhereHas('metas', fn (Builder $meta) => $meta->where('meta_value', 'like', '%'.$value.'%'));
        });
    }

    private function actions(AppointmentUser $appointment, array $item, Carbon $now, ConsultationPractitioner $practitioner): array
    {
        $starts = $item['starts_at'] ? Carbon::parse($item['starts_at']) : null;
        $ends = $item['ends_at'] ? Carbon::parse($item['ends_at']) : null;
        $closed = $appointment->consultationCase?->isClosed() ?? false;
        $reports = (int) $appointment->consultation_reports_count;
        $answered = $appointment->callLogs->filter(fn ($call) => $call->occurredDuringAppointment() && $call->final_result === 'ANSWERED')->count();
        $voipReady = $this->softphones->for($practitioner)['configured'];
        $autoAfter = $starts?->copy()->addMinutes(6);

        return [
            'complete' => $this->action(! $closed && $reports > 0, $closed ? 'case_closed' : 'report_required', $closed ? 'پرونده نوبت بسته شده است.' : 'ابتدا حداقل یک گزارش ثبت کنید.', ['requires_report' => true, 'reports_count' => $reports]),
            'no_show' => $this->action(! $closed && $ends && $now->gt($ends) && $answered === 0, $closed ? 'case_closed' : (!$ends || $now->lte($ends) ? 'appointment_not_ended' : 'answered_call_in_window'), $closed ? 'پرونده نوبت بسته شده است.' : (!$ends || $now->lte($ends) ? 'بازه نوبت هنوز تمام نشده است.' : 'در بازه نوبت تماس موفق ثبت شده است.')),
            'auto_call' => $this->action(! $closed && $voipReady && $autoAfter && $ends && $now->gte($autoAfter) && $now->lte($ends), $closed ? 'case_closed' : (!$voipReady ? 'voip_not_configured' : (!$autoAfter || $now->lt($autoAfter) ? 'too_early' : 'appointment_ended')), $closed ? 'پرونده نوبت بسته شده است.' : (!$voipReady ? 'تنظیمات Softphone کامل نیست.' : (!$autoAfter || $now->lt($autoAfter) ? 'تماس خودکار شش دقیقه پس از شروع نوبت مجاز می‌شود.' : 'بازه نوبت پایان یافته است.')), ['allowed_after' => $autoAfter?->toIso8601String()]),
            'settlement' => $this->action($ends && $now->gt($ends) && $appointment->dashboard['financial'] !== 'settled', !$ends || $now->lte($ends) ? 'appointment_not_ended' : 'already_settled', !$ends || $now->lte($ends) ? 'تسویه پس از پایان نوبت مجاز است.' : 'تسویه قبلاً نهایی شده است.', ['status' => $appointment->dashboard['financial']]),
        ];
    }

    private function action(bool $allowed, ?string $code, ?string $reason, array $extra = []): array
    { return ['allowed' => $allowed, 'reason_code' => $allowed ? null : $code, 'reason' => $allowed ? null : $reason] + $extra; }

    private function clock(): array
    {
        $timezone = (string) (ConsultationSetting::current()->timezone ?: config('app.timezone', 'Asia/Tehran'));
        return [$timezone, now($timezone)];
    }
}
