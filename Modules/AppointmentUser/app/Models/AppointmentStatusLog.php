<?php

namespace Modules\AppointmentUser\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Events\AppointmentStatusChanged;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

/**
 * تاریخچه تغییر وضعیت نوبت‌ها. فقط نوشته میشود و به نوبت وابسته نیست،
 * پس با حذف نوبت هم باقی می‌ماند.
 */
class AppointmentStatusLog extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_CREATED = 'created';
    public const EVENT_STATUS_CHANGED = 'status_changed';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_RESTORED = 'restored';

    protected $guarded = ['id'];

    protected $casts = [
        'from_status' => AppointmentUserStatusEnum::class,
        'to_status' => AppointmentUserStatusEnum::class,
        'date_visit' => 'date',
        'created_at' => 'datetime',
    ];

    public static function eventLabels(): array
    {
        return [
            self::EVENT_CREATED => 'ثبت نوبت',
            self::EVENT_STATUS_CHANGED => 'تغییر وضعیت',
            self::EVENT_DELETED => 'حذف نوبت',
            self::EVENT_RESTORED => 'بازیابی نوبت',
        ];
    }

    /**
     * فقط رویداد را با داده‌های سبک همان لحظه (کاربر، IP، منبع) پخش می‌کند؛
     * نوشتن رکورد در listener انجام می‌شود (در صورت فعال بودن صف، بیرون از درخواست).
     * خطا در ثبت لاگ نباید عملیات اصلی را خراب کند.
     */
    public static function record(AppointmentUser $appointment, string $event, ?AppointmentUserStatusEnum $from, ?AppointmentUserStatusEnum $to): void
    {
        try {
            $actorId = auth()->id();
            $source = app()->runningInConsole()
                ? 'console'
                : ($actorId ? (request()->is('api/*') ? 'api' : 'web') : 'system');

            event(new AppointmentStatusChanged(
                appointmentId: (int) $appointment->getKey(),
                trackingCode: $appointment->tracking_code,
                event: $event,
                fromStatus: $from?->value,
                toStatus: $to?->value,
                actorId: $actorId ? (int) $actorId : null,
                source: $source,
                ip: request()->ip(),
                patientId: $appointment->user_id ? (int) $appointment->user_id : null,
                doctorId: $appointment->doctor_id ? (int) $appointment->doctor_id : null,
                dateVisit: $appointment->date_visit ? \Illuminate\Support\Carbon::parse($appointment->date_visit)->toDateString() : null,
                startTime: $appointment->start_time ? (string) $appointment->start_time : null,
                occurredAt: now()->toDateTimeString(),
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}