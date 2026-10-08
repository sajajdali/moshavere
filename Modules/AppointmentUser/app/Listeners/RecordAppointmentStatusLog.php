<?php

namespace Modules\AppointmentUser\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\AppointmentUser\app\Events\AppointmentStatusChanged;
use Modules\AppointmentUser\app\Models\AppointmentStatusLog;
use Modules\User\Entities\User;

/**
 * نوشتن تاریخچه وضعیت نوبت. در صورت فعال بودن صف، خارج از درخواست کاربر
 * (و بعد از commit تراکنش) اجرا میشود تا ذخیره نوبت سنگین نشود.
 */
class RecordAppointmentStatusLog implements ShouldQueue
{
    public bool $afterCommit = true;

    public int $tries = 3;

    public function handle(AppointmentStatusChanged $e): void
    {
        $names = User::withoutGlobalScopes()
            ->whereIn('id', array_filter([$e->actorId, $e->patientId, $e->doctorId]))
            ->get()
            ->keyBy('id');

        AppointmentStatusLog::create([
            'appointment_user_id' => $e->appointmentId,
            'tracking_code' => $e->trackingCode,
            'event' => $e->event,
            'from_status' => $e->fromStatus,
            'to_status' => $e->toStatus,
            'changed_by' => $e->actorId,
            'changed_by_name' => $names->get($e->actorId ?? 0)?->full_name ?? null,
            'source' => $e->source,
            'ip' => $e->ip,
            'patient_id' => $e->patientId,
            'patient_name' => $names->get($e->patientId ?? 0)?->full_name ?? null,
            'patient_mobile' => $names->get($e->patientId ?? 0)?->mobile ?? null,
            'doctor_id' => $e->doctorId,
            'doctor_name' => $names->get($e->doctorId ?? 0)?->full_name ?? null,
            'date_visit' => $e->dateVisit,
            'start_time' => $e->startTime,
            'created_at' => $e->occurredAt,
        ]);
    }
}


