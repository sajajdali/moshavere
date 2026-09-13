<?php

namespace Modules\OnlineConsultation\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Support\ConsultationAccess;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;

class SendTomorrowAppointmentSummary implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $profileId,
        public string $date,
        public string $template,
        public ?string $tenantId = null,
    ) {}

    public function uniqueId(): string
    {
        return implode(':', [$this->tenantId, $this->profileId, $this->date]);
    }

    public function handle(): void
    {
        if ($this->tenantId && ! tenancy()->initialized) {
            tenancy()->initialize($this->tenantId);
        }

        if (! ConsultationAccess::enabled() || ! (bool) Setting::v(SettingKeyEnum::CONSULT_SMS_PRACTITIONER_DAILY_ACTIVE)) {
            return;
        }

        $profile = ConsultationPractitioner::with('user')->find($this->profileId);
        if (! $profile?->active || ! $profile->tomorrow_schedule_sms_enabled || blank($profile->user?->mobile)) {
            return;
        }

        $day = CarbonImmutable::parse($this->date, config('app.timezone'));
        $appointments = $profile->appointments()
            ->with('user')
            ->where('kind', AppointmentUserKindEnum::VOIP->value)
            ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
            ->whereBetween('date_visit', [$day->startOfDay(), $day->endOfDay()])
            ->orderBy('date_visit')
            ->orderBy('start_time')
            ->get();

        // Never send an empty report.
        if ($appointments->isEmpty()) {
            return;
        }

        $lines = $appointments->map(function ($appointment): string {
            $time = substr((string) ($appointment->start_time ?: $appointment->date_visit?->format('H:i')), 0, 5);
            $time = preg_replace('/^0/', '', $time);
            $time = preg_replace('/:00$/', '', $time);
            $name = trim((string) $appointment->user?->fullName) ?: 'بیمار';

            return $this->persianDigits($time).' '.$name;
        });
        $message = 'فردا:'.$this->persianDigits((string) $appointments->count())." نوبت\n".$lines->implode("\n");
        $deduplicationKey = "practitioner_tomorrow_schedule:{$profile->user_id}:{$this->date}";

        $delivery = ConsultationSmsDelivery::firstOrCreate(
            ['deduplication_key' => $deduplicationKey],
            [
                'practitioner_id' => $profile->user_id,
                'type' => 'practitioner_tomorrow_schedule',
                'recipient_type' => 'practitioner',
                'rule_title' => 'برنامه نوبت‌های فردا',
                'recipient' => $profile->user->mobile,
                'template' => $this->template,
                'scheduled_at' => now(),
                'payload' => ['params' => [$message], 'message_text' => $message],
            ],
        );

        if ($delivery->wasRecentlyCreated) {
            SendConsultationSms::dispatch($delivery->id, $this->tenantId);
        }
    }

    private function persianDigits(string $value): string
    {
        return strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }
}
