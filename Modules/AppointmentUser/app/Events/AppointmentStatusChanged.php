<?php

namespace Modules\AppointmentUser\app\Events;

/**
 * رویداد سبک برای تاریخچه وضعیت نوبت. فقط داده ساده دارد (بدون مدل)،
 * تا هنگام صف شدن، حتی بعد از حذف نوبت هم قابل پردازش باشد.
 * زمان رویداد و کاربر/IP همان لحظه تغییر برداشته میشوند، نه لحظه اجرای صف.
 */
class AppointmentStatusChanged
{
    public function __construct(
        public int $appointmentId,
        public ?string $trackingCode,
        public string $event,
        public ?int $fromStatus,
        public ?int $toStatus,
        public ?int $actorId,
        public string $source,
        public ?string $ip,
        public ?int $patientId,
        public ?int $doctorId,
        public ?string $dateVisit,
        public ?string $startTime,
        public string $occurredAt,
    ) {}
}
