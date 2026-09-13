<?php

namespace Modules\OnlineConsultation\Console;

use Illuminate\Console\Command;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Jobs\SendTomorrowAppointmentSummary;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Services\ConsultationReminderScheduler;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class DispatchConsultationSms extends Command
{
    protected $signature = 'consultation:dispatch-sms';

    protected $description = 'Create and dispatch due automatic consultation SMS messages';

    public function handle(ConsultationReminderScheduler $reminders): int
    {
        if (! ConsultationAccess::enabled()) {
            return self::SUCCESS;
        }

        $reminders->syncDueCandidates();
        $reminders->dispatchDue();
        $this->dispatchTomorrowSchedules();

        return self::SUCCESS;
    }

    private function dispatchTomorrowSchedules(): void
    {
        if (! $this->enabled(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_ACTIVE) || blank($template = Setting::v(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TEMPLATE))) {
            return;
        }
        $time = Setting::v(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_TIME) ?: '23:00';
        $scheduled = now()->startOfDay()->setTimeFromTimeString($time);
        if (now()->lt($scheduled)) {
            return;
        }
        $date = now()->addDay()->toDateString();
        $tomorrow = now()->addDay();
        foreach (ConsultationPractitioner::where('active', true)
            ->where('tomorrow_schedule_sms_enabled', true)
            ->whereHas('appointments', fn ($query) => $query
                ->where('kind', AppointmentUserKindEnum::VOIP->value)
                ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
                ->whereBetween('date_visit', [$tomorrow->copy()->startOfDay(), $tomorrow->copy()->endOfDay()]))
            ->get() as $profile) {
            $deduplicationKey = "practitioner_tomorrow_schedule:{$profile->user_id}:{$date}";
            if (! ConsultationSmsDelivery::where('deduplication_key', $deduplicationKey)->exists()) {
                SendTomorrowAppointmentSummary::dispatch($profile->id, $date, $template, tenant()?->getTenantKey());
            }
        }
    }

    private function enabled(SettingKeyEnum $key): bool
    {
        return (bool) Setting::v($key);
    }

}
