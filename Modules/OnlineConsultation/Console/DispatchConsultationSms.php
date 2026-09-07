<?php

namespace Modules\OnlineConsultation\Console;

use Illuminate\Console\Command;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Jobs\SendConsultationSms;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class DispatchConsultationSms extends Command
{
    protected $signature = 'consultation:dispatch-sms';

    protected $description = 'Create and dispatch due automatic consultation SMS messages';

    public function handle(ConsultantDashboardService $dashboard): int
    {
        if (! ConsultationAccess::enabled()) {
            return self::SUCCESS;
        }

        foreach ($this->appointmentRules() as $type => $rule) {
            if (! $this->enabled($rule['active']) || blank($template = Setting::v($rule['template']))) {
                continue;
            }
            $minutes = max(0, (int) (Setting::v($rule['minutes']) ?? $rule['default']));
            AppointmentUser::with(['user', 'doctor'])->whereIn('kind', [AppointmentUserKindEnum::ONLINE->value, AppointmentUserKindEnum::VOIP->value])
                ->whereNotIn('status', [AppointmentUserStatusEnum::STATUS_CANCEL->value, AppointmentUserStatusEnum::STATUS_DISAPPROVED->value])
                ->whereBetween('date_visit', [now()->addMinutes($minutes)->subMinute(), now()->addMinutes($minutes)->addMinute()])->chunkById(100, function ($appointments) use ($type, $rule, $minutes, $template) {
                    foreach ($appointments as $appointment) {
                        $recipient = $rule['recipient'] === 'patient' ? $appointment->user?->mobile : $appointment->doctor?->mobile;
                        $this->create($type, $appointment->id, $appointment->doctor_id, $recipient, $template, $appointment->date_visit->copy()->subMinutes($minutes), [
                            $rule['recipient'] === 'patient' ? $appointment->user?->fullName : $appointment->doctor?->fullName,
                            verta($appointment->date_visit)->format('Y/m/d H:i'), $appointment->tracking_code ?: $appointment->id,
                        ]);
                    }
                });
        }
        $this->dispatchDailyReports($dashboard);

        return self::SUCCESS;
    }

    private function dispatchDailyReports(ConsultantDashboardService $dashboard): void
    {
        if (! $this->enabled(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_ACTIVE) || blank($template = Setting::v(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TEMPLATE))) {
            return;
        }
        $time = Setting::v(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TIME) ?: '21:00';
        $scheduled = now()->startOfDay()->setTimeFromTimeString($time);
        if (now()->lt($scheduled)) {
            return;
        }
        [$from, $to] = $dashboard->period('today');
        foreach (ConsultationPractitioner::with('user')->where('active', true)->get() as $profile) {
            $items = $dashboard->query($from, $to, $profile->user_id)->with(['callLogs', 'billingRecord.adjustments'])->get();
            $stats = $dashboard->stats($items);
            $this->create('practitioner_daily_report', null, $profile->user_id, $profile->user?->mobile, $template, $scheduled, [
                $profile->display_name, $stats['total'], $stats['completed'], $stats['missed'], $stats['unanswered'], (int) round($stats['talk_seconds'] / 60),
            ], now()->toDateString());
        }
    }

    private function create(string $type, ?int $appointmentId, ?int $practitionerId, ?string $recipient, string $template, $scheduledAt, array $params, ?string $scope = null): void
    {
        $key = implode(':', [$type, $appointmentId ?: $practitionerId, $scope ?: 'appointment']);
        $delivery = ConsultationSmsDelivery::firstOrCreate(['deduplication_key' => $key], ['appointment_id' => $appointmentId, 'practitioner_id' => $practitionerId, 'type' => $type, 'recipient' => $recipient ?: '', 'template' => $template, 'scheduled_at' => $scheduledAt, 'payload' => ['params' => $params]]);
        if ($delivery->wasRecentlyCreated) {
            SendConsultationSms::dispatch($delivery->id, tenant()?->getTenantKey());
        }
    }

    private function enabled(SettingKeyEnum $key): bool
    {
        return (bool) Setting::v($key);
    }

    private function appointmentRules(): array
    {
        return [
            'patient_first_reminder' => ['active' => SettingKeyEnum::CONSULT_SMS_PATIENT_FIRST_ACTIVE, 'template' => SettingKeyEnum::CONSULT_SMS_PATIENT_FIRST_TEMPLATE, 'minutes' => SettingKeyEnum::CONSULT_SMS_PATIENT_FIRST_MINUTES, 'default' => 180, 'recipient' => 'patient'],
            'patient_second_reminder' => ['active' => SettingKeyEnum::CONSULT_SMS_PATIENT_SECOND_ACTIVE, 'template' => SettingKeyEnum::CONSULT_SMS_PATIENT_SECOND_TEMPLATE, 'minutes' => SettingKeyEnum::CONSULT_SMS_PATIENT_SECOND_MINUTES, 'default' => 60, 'recipient' => 'patient'],
            'patient_final_reminder' => ['active' => SettingKeyEnum::CONSULT_SMS_PATIENT_FINAL_ACTIVE, 'template' => SettingKeyEnum::CONSULT_SMS_PATIENT_FINAL_TEMPLATE, 'minutes' => SettingKeyEnum::CONSULT_SMS_PATIENT_FINAL_MINUTES, 'default' => 15, 'recipient' => 'patient'],
            'practitioner_appointment_reminder' => ['active' => SettingKeyEnum::CONSULT_SMS_PRACTITIONER_REMINDER_ACTIVE, 'template' => SettingKeyEnum::CONSULT_SMS_PRACTITIONER_REMINDER_TEMPLATE, 'minutes' => SettingKeyEnum::CONSULT_SMS_PRACTITIONER_REMINDER_MINUTES, 'default' => 15, 'recipient' => 'practitioner'],
        ];
    }
}
