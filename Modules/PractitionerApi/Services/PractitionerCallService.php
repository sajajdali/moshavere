<?php
namespace Modules\PractitionerApi\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentCallbackRequest;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultationCallbackService;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerCallService
{
    public function __construct(private readonly ConsultationCallbackService $callbacks, private readonly PractitionerSoftphoneService $softphones) {}

    public function calls(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $calls = $appointment->callLogs()->orderBy('call_entered_at')->get();
        return ['data' => $calls->values()->map(fn ($call, $index) => $this->present($call, $index + 1))->all(), 'meta' => [
            'total' => $calls->count(), 'answered' => $calls->where('final_result', 'ANSWERED')->count(),
            'missed' => $calls->where('final_result', '<>', 'ANSWERED')->count(),
            'talk_seconds' => (int) $calls->where('final_result', 'ANSWERED')->sum('talk_duration_seconds'),
        ]];
    }

    public function active(ConsultationPractitioner $practitioner): ?array
    {
        $call = $this->callQuery($practitioner)->whereNull('ended_at')
            ->where(function (Builder $query) { $query->whereNull('final_result')->orWhereIn('final_result', ['RINGING', 'ANSWERED', 'IN_PROGRESS']); })
            ->latest('call_entered_at')->first();
        if ($call) return $this->present($call, 1) + ['appointment_id' => (int) $call->appointment_id, 'has_call' => true];

        $appointment = $this->activeAppointment($practitioner);
        if (! $appointment) return null;

        [$startsAt, $endsAt] = $this->appointmentWindow($appointment);
        return [
            // CallRecord clients expect an integer id. Zero explicitly means that
            // this context was inferred from the current appointment, not a call log.
            'id' => 0, 'sequence' => 0, 'appointment_id' => (int) $appointment->id,
            'direction' => '', 'started_at' => null, 'answered_at' => null, 'ended_at' => null,
            'duration_seconds' => 0, 'result' => '', 'early' => false, 'ended_by' => 'none',
            'channel' => 'voip', 'note' => null, 'has_call' => false,
            'appointment' => [
                'id' => (int) $appointment->id, 'file_no' => $appointment->tracking_code,
                'starts_at' => $startsAt->toIso8601String(), 'ends_at' => $endsAt->toIso8601String(),
                'patient' => [
                    'id' => $appointment->user_id ? (int) $appointment->user_id : null,
                    'full_name' => $appointment->user?->fullName,
                    'mobile' => $appointment->user?->mobile,
                ],
            ],
        ];
    }

    private function activeAppointment(ConsultationPractitioner $practitioner): ?AppointmentUser
    {
        $timezone = (string) (\Modules\OnlineConsultation\Models\ConsultationSetting::current()->timezone ?: config('app.timezone', 'Asia/Tehran'));
        $now = now($timezone);

        return AppointmentUser::query()->with(['user', 'consultationCase'])
            ->where('doctor_id', $practitioner->user_id)
            ->whereIn('kind', [AppointmentUserKindEnum::ONLINE->value, AppointmentUserKindEnum::VOIP->value])
            ->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)
            ->whereBetween('date_visit', [
                $now->copy()->subDay()->startOfDay()->setTimezone(config('app.timezone')),
                $now->copy()->endOfDay()->setTimezone(config('app.timezone')),
            ])
            ->orderBy('date_visit')->get()
            ->first(function (AppointmentUser $appointment) use ($now): bool {
                if ($appointment->consultationCase?->isClosed()) return false;
                [$startsAt, $endsAt] = $this->appointmentWindow($appointment);
                return $now->betweenIncluded($startsAt, $endsAt);
            });
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function appointmentWindow(AppointmentUser $appointment): array
    {
        $timezone = (string) (\Modules\OnlineConsultation\Models\ConsultationSetting::current()->timezone ?: config('app.timezone', 'Asia/Tehran'));
        $date = $appointment->date_visit->copy()->setTimezone($timezone)->toDateString();
        $start = Carbon::parse($date.' '.($appointment->start_time ?: $appointment->date_visit->format('H:i:s')), $timezone);
        $end = $appointment->end_time ? Carbon::parse($date.' '.$appointment->end_time, $timezone) : $start->copy()->addMinutes(30);
        if ($end->lte($start)) $end->addDay();
        return [$start, $end];
    }

    public function note(ConsultationPractitioner $practitioner, int $callId, ?string $note): array
    {
        $call = $this->callQuery($practitioner)->whereKey($callId)->first();
        if (! $call) throw new NotFoundHttpException('تماس برای این پزشک یافت نشد.');
        $call->update(['practitioner_note' => filled($note) ? trim($note) : null]);
        return ['id' => (int) $call->id, 'appointment_id' => (int) $call->appointment_id, 'note' => $call->practitioner_note];
    }

    public function autoCall(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $availability = $this->callbacks->availability($appointment);
        if (! $availability['available']) throw new ConflictHttpException((string) $availability['reason']);
        if (! $this->softphones->for($practitioner)['configured']) throw new ConflictHttpException('تنظیمات Softphone پزشک کامل نیست.');
        $duplicate = AppointmentCallbackRequest::where('appointment_id', $appointment->id)
            ->whereIn('status', [AppointmentCallbackRequest::STATUS_PENDING, AppointmentCallbackRequest::STATUS_ACCEPTED])
            ->where('requested_at', '>=', now()->subMinute())->exists();
        if ($duplicate) throw new ConflictHttpException('درخواست تماس اخیر هنوز معتبر است و نباید تکرار شود.');
        try { $callback = $this->callbacks->request($appointment, $practitioner, $practitioner->user); }
        catch (\RuntimeException $exception) { throw new HttpException(503, $exception->getMessage(), $exception); }
        return ['request_id' => $callback->request_id, 'appointment_id' => (int) $appointment->id, 'state' => strtolower($callback->status), 'requested_at' => $callback->requested_at?->toIso8601String(), 'extension' => $callback->advisor_extension];
    }

    public function updateVoip(ConsultationPractitioner $practitioner, array $data): array
    {
        $duplicate = ConsultationPractitioner::where('extension', $data['extension'])
            ->where('id', '<>', $practitioner->id)->exists();
        if ($duplicate) throw ValidationException::withMessages(['extension' => ['این داخلی قبلاً استفاده شده است.']]);
        $values = ['extension' => $data['extension'], 'sip_username' => $data['username']];
        if (filled($data['password'] ?? null)) $values['sip_secret'] = $data['password'];
        $practitioner->update($values);
        return $this->softphones->for($practitioner->fresh());
    }

    private function ownedAppointment(ConsultationPractitioner $practitioner, int $id): AppointmentUser
    {
        $appointment = AppointmentUser::query()->with('user')->whereKey($id)->where('doctor_id', $practitioner->user_id)->first();
        if (! $appointment) throw new NotFoundHttpException('نوبت برای این پزشک یافت نشد.');
        return $appointment;
    }
    private function callQuery(ConsultationPractitioner $practitioner): Builder
    { return AppointmentCallLog::query()->whereHas('appointment', fn (Builder $q) => $q->where('doctor_id', $practitioner->user_id)); }
    private function present(AppointmentCallLog $call, int $sequence): array
    { return ['id' => (int) $call->id, 'sequence' => $sequence, 'direction' => strtolower((string) $call->direction), 'started_at' => $call->call_entered_at?->toIso8601String(), 'answered_at' => $call->answered_at?->toIso8601String(), 'ended_at' => $call->ended_at?->toIso8601String(), 'duration_seconds' => (int) $call->talk_duration_seconds, 'result' => strtolower((string) $call->final_result), 'early' => $call->isEarlyCall(), 'ended_by' => strtolower((string) ($call->disconnected_by ?: 'none')), 'channel' => strtolower((string) ($call->connection_type ?: 'voip')), 'note' => $call->practitioner_note]; }
}
