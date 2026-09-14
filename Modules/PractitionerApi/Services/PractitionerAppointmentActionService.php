<?php

namespace Modules\PractitionerApi\Services;

use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentConsultationCase;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultationCaseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerAppointmentActionService
{
    public function __construct(
        private readonly ConsultationCaseService $cases,
        private readonly PractitionerAppointmentService $appointments,
    ) {}

    public function complete(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $wasCompleted = $appointment->consultationCase?->state === AppointmentConsultationCase::STATE_COMPLETED;
        if ($appointment->consultationCase?->state === AppointmentConsultationCase::STATE_PATIENT_NO_SHOW) {
            throw ValidationException::withMessages(['completion' => ['این نوبت قبلاً با وضعیت عدم حضور بیمار نهایی شده است.']]);
        }
        $case = $this->cases->complete($appointment, $practitioner->user);

        return [
            'operation' => 'complete', 'case_state' => $case->state, 'idempotent' => $wasCompleted,
            'appointment' => $this->appointments->detail($practitioner, $appointmentId),
        ];
    }

    public function noShow(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $wasNoShow = $appointment->consultationCase?->state === AppointmentConsultationCase::STATE_PATIENT_NO_SHOW;
        $case = $this->cases->markPatientNoShow($appointment, $practitioner->user);

        return [
            'operation' => 'no_show', 'case_state' => $case->state, 'idempotent' => $wasNoShow,
            'appointment' => $this->appointments->detail($practitioner, $appointmentId),
        ];
    }

    private function ownedAppointment(ConsultationPractitioner $practitioner, int $id): AppointmentUser
    {
        $appointment = AppointmentUser::query()->with('consultationCase')->whereKey($id)
            ->where('doctor_id', $practitioner->user_id)->first();
        if (! $appointment) throw new NotFoundHttpException('نوبت برای این پزشک یافت نشد.');
        return $appointment;
    }
}
