<?php

namespace Modules\OnlineConsultation\Console;

use Illuminate\Console\Command;
use Modules\OnlineConsultation\Jobs\SendConsultationSms;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Modules\OnlineConsultation\Services\ConsultationReminderScheduler;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class DispatchConsultationSms extends Command
{
    protected $signature = 'consultation:dispatch-sms';

    protected $description = 'Create and dispatch due automatic consultation SMS messages';

    public function handle(ConsultantDashboardService $dashboard, ConsultationReminderScheduler $reminders): int
    {
        if (! ConsultationAccess::enabled()) {
            return self::SUCCESS;
        }

        $reminders->syncDueCandidates();
        $reminders->dispatchDue();
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
        $delivery = ConsultationSmsDelivery::firstOrCreate(['deduplication_key' => $key], ['appointment_id' => $appointmentId, 'practitioner_id' => $practitionerId, 'type' => $type, 'recipient_type' => 'practitioner', 'rule_title' => 'گزارش پایان روز مشاور', 'recipient' => $recipient ?: '', 'template' => $template, 'scheduled_at' => $scheduledAt, 'payload' => ['params' => $params]]);
        if ($delivery->wasRecentlyCreated) {
            SendConsultationSms::dispatch($delivery->id, tenant()?->getTenantKey());
        }
    }

    private function enabled(SettingKeyEnum $key): bool
    {
        return (bool) Setting::v($key);
    }

}
