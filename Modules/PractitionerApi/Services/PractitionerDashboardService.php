<?php

namespace Modules\PractitionerApi\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;

class PractitionerDashboardService
{
    public function __construct(
        private readonly ConsultantDashboardService $consultations,
        private readonly PractitionerSoftphoneService $softphones,
    ) {
    }

    /** @return array<string, mixed> */
    public function get(ConsultationPractitioner $practitioner, ?string $date = null): array
    {
        $settings = ConsultationSetting::current();
        $timezone = (string) ($settings->timezone ?: config('app.timezone', 'Asia/Tehran'));
        $serverTime = now($timezone);
        $selectedDate = $date
            ? Carbon::createFromFormat('Y-m-d', $date, $timezone)->startOfDay()
            : $serverTime->copy()->startOfDay();
        $from = $selectedDate->copy()->setTimezone(config('app.timezone'));
        $to = $selectedDate->copy()->endOfDay()->setTimezone(config('app.timezone'));

        $appointments = $this->consultations
            ->query($from, $to, $practitioner->user_id)
            ->with([
                'user',
                'service:id,title',
                'place:id,title',
                'callLogs',
                'billingRecord.adjustments',
                'consultationCase',
            ])
            ->orderBy('date_visit')
            ->get()
            ->map(fn (AppointmentUser $appointment) => $this->consultations->decorate($appointment));

        $stats = $this->consultations->stats($appointments);
        $presented = $appointments->map(fn (AppointmentUser $appointment) => $this->presentAppointment($appointment, $timezone, $serverTime));
        $next = $presented
            ->filter(fn (array $appointment) => $appointment['ends_at'] !== null && Carbon::parse($appointment['ends_at'])->gt($serverTime))
            ->sortBy('starts_at')
            ->first();
        $upcoming = $presented
            ->filter(fn (array $appointment) => in_array($appointment['phase'], ['upcoming', 'in_progress'], true))
            ->filter(fn (array $appointment) => $next === null || $appointment['id'] !== $next['id'])
            ->values()
            ->all();
        $softphone = $this->softphones->for($practitioner);

        return [
            'server_time' => $serverTime->toIso8601String(),
            'timezone' => $timezone,
            'date' => $selectedDate->toDateString(),
            'date_jalali' => verta($selectedDate)->format('Y-m-d'),
            'date_label' => $this->dateLabel($selectedDate, $serverTime),
            'practitioner' => [
                'id' => (int) $practitioner->id,
                'display_name' => (string) $practitioner->display_name,
                'availability' => (string) $practitioner->availability,
                'active' => (bool) $practitioner->active,
                'app_access' => (bool) $practitioner->app_access,
            ],
            'booking' => ['enabled' => (bool) $settings->booking_enabled],
            'voip' => [
                'configured' => $softphone['configured'],
                'connected' => null,
                'status' => $softphone['configured'] ? 'configuration_ready' : 'configuration_incomplete',
                'extension' => $softphone['extension'],
                'server_host' => $softphone['server_host'],
                'server_port' => $softphone['server_port'],
                'transport' => $softphone['transport'],
                'missing_fields' => $softphone['missing_fields'],
            ],
            'next_appointment' => $next,
            'upcoming_today' => $upcoming,
            'today_stats' => [
                'total' => (int) $stats['total'],
                'completed' => (int) $stats['completed'],
                'scheduled' => (int) $stats['remaining'],
                'failed' => (int) $stats['missed'] + (int) $stats['patient_no_show'],
                'booked_minutes' => (int) $appointments->sum(fn (AppointmentUser $appointment) => $appointment->dashboard['reserved_minutes']),
                'calls' => (int) $stats['calls'],
                'answered_calls' => (int) $stats['answered'],
                'talk_seconds' => (int) $stats['talk_seconds'],
            ],
            'important_messages' => $this->importantMessages($appointments),
            'financial_summary' => [
                'settled_paid_amount' => (int) $stats['income'],
                'pending_paid_amount' => (int) $stats['pending_income'],
                'practitioner_earned_amount' => (int) $stats['practitioner_income'],
                'refundable_amount' => (int) $stats['refundable'],
                'refunded_amount' => (int) $stats['refunded'],
                'currency' => 'TOMAN',
            ],
            'empty_state' => [
                'show' => $appointments->isEmpty(),
                'message' => $appointments->isEmpty()
                    ? ((bool) $settings->booking_enabled
                        ? 'برای این روز نوبتی ثبت نشده است؛ نوبت‌دهی فعال است.'
                        : 'برای این روز نوبتی ثبت نشده و نوبت‌دهی نیز غیرفعال است.')
                    : null,
            ],
            'week_strip' => $this->weekStrip($practitioner, $selectedDate, $serverTime, $timezone),
        ];
    }

    /** @return array<string, mixed> */
    public function presentAppointment(AppointmentUser $appointment, string $timezone, Carbon $serverTime): array
    {
        [$startsAt, $endsAt] = $this->appointmentWindow($appointment, $timezone);
        $phase = ! $startsAt || ! $endsAt ? 'unknown'
            : ($serverTime->lt($startsAt) ? 'upcoming' : ($serverTime->lte($endsAt) ? 'in_progress' : 'past'));
        $section = $appointment->service ?: $appointment->place;

        return [
            'id' => (int) $appointment->id,
            'file_no' => $appointment->tracking_code,
            'starts_at' => $startsAt?->toIso8601String(),
            'ends_at' => $endsAt?->toIso8601String(),
            'duration_minutes' => $startsAt && $endsAt ? $startsAt->diffInMinutes($endsAt) : 0,
            'phase' => $phase,
            'status' => (string) $appointment->dashboard['status'],
            'status_reason' => $appointment->dashboard['reason'],
            'countdown' => [
                'starts_in_seconds' => $startsAt ? $serverTime->diffInSeconds($startsAt, false) : null,
                'ends_in_seconds' => $endsAt ? $serverTime->diffInSeconds($endsAt, false) : null,
            ],
            'patient' => [
                'id' => $appointment->user_id ? (int) $appointment->user_id : null,
                'full_name' => $appointment->user?->fullName ?: ($appointment->user_id ? 'کاربر '.$appointment->user_id : null),
                'mobile' => $appointment->user?->mobile,
            ],
            'section' => [
                'key' => $appointment->service_id ? 'service:'.$appointment->service_id : ($appointment->place_id ? 'place:'.$appointment->place_id : 'general'),
                'name' => $section?->title ?: 'مشاوره آنلاین',
            ],
            'complaint_summary' => data_get($appointment->details, AppointmentUser::DETAIL_DESCRIPTION)
                ?: data_get($appointment->details, AppointmentUser::DETAIL_QUESTION),
            'calls_summary' => [
                'total' => (int) $appointment->dashboard['calls'],
                'answered' => (int) $appointment->dashboard['answered'],
                'unanswered' => (int) $appointment->dashboard['unanswered'],
                'talk_seconds' => (int) $appointment->dashboard['talk_seconds'],
            ],
        ];
    }

    /** @return array{0: Carbon|null, 1: Carbon|null} */
    private function appointmentWindow(AppointmentUser $appointment, string $timezone): array
    {
        if (! $appointment->date_visit) {
            return [null, null];
        }

        $date = $appointment->date_visit->copy()->setTimezone($timezone)->toDateString();
        $start = Carbon::parse($date.' '.($appointment->start_time ?: $appointment->date_visit->format('H:i:s')), $timezone);
        $end = $appointment->end_time
            ? Carbon::parse($date.' '.$appointment->end_time, $timezone)
            : $start->copy()->addMinutes(30);
        if ($end->lte($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    /** @return list<array{type: string, appointment_id: int, message: string}> */
    private function importantMessages(Collection $appointments): array
    {
        return $appointments
            ->filter(fn (AppointmentUser $appointment) => (bool) $appointment->dashboard['alert'])
            ->map(fn (AppointmentUser $appointment) => [
                'type' => 'appointment_attention',
                'appointment_id' => (int) $appointment->id,
                'message' => (string) ($appointment->dashboard['reason'] ?: 'این نوبت نیازمند بررسی است.'),
            ])->values()->all();
    }

    /** @return list<array<string, mixed>> */
    private function weekStrip(ConsultationPractitioner $practitioner, Carbon $selectedDate, Carbon $today, string $timezone): array
    {
        $start = $selectedDate->copy()->subDays(3)->startOfDay();
        $end = $selectedDate->copy()->addDays(3)->endOfDay();
        $counts = $this->consultations
            ->query($start->copy()->setTimezone(config('app.timezone')), $end->copy()->setTimezone(config('app.timezone')), $practitioner->user_id)
            ->get(['date_visit'])
            ->countBy(fn (AppointmentUser $appointment) => $appointment->date_visit->copy()->setTimezone($timezone)->toDateString());

        return collect(range(0, 6))->map(function (int $offset) use ($start, $selectedDate, $today, $counts): array {
            $date = $start->copy()->addDays($offset);

            return [
                'date' => $date->toDateString(),
                'date_jalali' => verta($date)->format('Y-m-d'),
                'label' => $date->isSameDay($today) ? 'امروز' : verta($date)->formatWord('l'),
                'day' => (int) verta($date)->format('d'),
                'count' => (int) $counts->get($date->toDateString(), 0),
                'is_selected' => $date->isSameDay($selectedDate),
                'is_today' => $date->isSameDay($today),
                'is_past' => $date->lt($today->copy()->startOfDay()),
            ];
        })->all();
    }

    private function dateLabel(Carbon $date, Carbon $today): string
    {
        $prefix = $date->isSameDay($today) ? 'امروز' : ($date->isSameDay($today->copy()->addDay()) ? 'فردا' : verta($date)->formatWord('l'));

        return $prefix.' — '.verta($date)->formatWord('j F Y');
    }
}
