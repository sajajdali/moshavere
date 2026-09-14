<?php

namespace Modules\PractitionerApi\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\AppointmentConsultationReport;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\ConsultationCaseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerReportService
{
    public function __construct(private readonly ConsultationCaseService $cases) {}

    public function reports(ConsultationPractitioner $practitioner, int $appointmentId): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $case = $appointment->consultationCase;

        return [
            'case' => ['state' => $case?->state ?? 'OPEN', 'closed' => $case?->isClosed() ?? false],
            'reports' => $appointment->consultationReports()->with('author')->oldest()->get()
                ->map(fn (AppointmentConsultationReport $report) => $this->present($report))->values()->all(),
            'outcomes' => collect(AppointmentConsultationReport::OUTCOMES)
                ->map(fn (string $label, string $value) => compact('value', 'label'))->values()->all(),
        ];
    }

    public function create(ConsultationPractitioner $practitioner, int $appointmentId, array $data): array
    {
        $appointment = $this->ownedAppointment($practitioner, $appointmentId);
        $report = $this->cases->addReport($appointment, $practitioner->user, $data);

        return $this->present($report->load('author'));
    }

    public function patientHistory(ConsultationPractitioner $practitioner, int $patientId): array
    {
        $appointments = AppointmentUser::query()
            ->where('doctor_id', $practitioner->user_id)->where('user_id', $patientId)
            ->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)
            ->with(['user', 'service', 'consultationReports.author'])
            ->latest('date_visit')->get();
        if ($appointments->isEmpty()) {
            throw new NotFoundHttpException('بیمار در سوابق این پزشک یافت نشد.');
        }

        $patient = $appointments->first()->user;
        return [
            'patient' => ['id' => (int) $patientId, 'name' => $patient?->fullName, 'mobile' => $patient?->mobile],
            'appointments' => $appointments->map(fn (AppointmentUser $appointment) => [
                'id' => (int) $appointment->id,
                'file_no' => $appointment->tracking_code,
                'visited_at' => $appointment->date_visit?->toIso8601String(),
                'service' => $appointment->service ? ['id' => (int) $appointment->service->id, 'title' => $appointment->service->title] : null,
                'reports' => $appointment->consultationReports->sortBy('created_at')->values()
                    ->map(fn (AppointmentConsultationReport $report) => $this->present($report))->all(),
            ])->all(),
        ];
    }

    private function ownedAppointment(ConsultationPractitioner $practitioner, int $id): AppointmentUser
    {
        $appointment = AppointmentUser::query()->whereKey($id)
            ->where('doctor_id', $practitioner->user_id)->first();
        if (! $appointment) throw new NotFoundHttpException('نوبت برای این پزشک یافت نشد.');
        return $appointment;
    }

    private function present(AppointmentConsultationReport $report): array
    {
        return [
            'id' => (int) $report->id,
            'appointment_id' => (int) $report->appointment_id,
            'outcome' => $report->outcome,
            'outcome_label' => AppointmentConsultationReport::OUTCOMES[$report->outcome] ?? $report->outcome,
            'subject' => $report->subject,
            'report_text' => $report->report_text,
            'follow_up_at' => $report->follow_up_at?->toIso8601String(),
            'created_at' => $report->created_at?->toIso8601String(),
            'author' => $report->author ? ['id' => (int) $report->author->id, 'name' => $report->author->fullName] : null,
        ];
    }
}
