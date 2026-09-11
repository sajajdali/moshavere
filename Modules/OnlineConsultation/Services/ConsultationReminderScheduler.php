<?php

namespace Modules\OnlineConsultation\Services;

use Illuminate\Support\Facades\Schema;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Jobs\SendConsultationSms;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Models\ConsultationSmsReminderRule;

class ConsultationReminderScheduler
{
    public function cancelPendingForAppointment(int $appointmentId, string $reason): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        ConsultationSmsDelivery::where('appointment_id', $appointmentId)
            ->whereNotNull('reminder_rule_id')
            ->whereIn('status', ['pending', 'queued', 'retrying'])
            ->update(['status' => 'skipped', 'error_message' => $reason]);
    }

    public function syncAppointment(AppointmentUser $appointment): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        $appointment->loadMissing(['user', 'doctor', 'service']);
        $eligible = $appointment->kind === AppointmentUserKindEnum::VOIP
            && $appointment->status === AppointmentUserStatusEnum::STATUS_SUCCESSFUL
            && $appointment->date_visit?->isFuture();

        if (! $eligible) {
            $this->cancelPendingForAppointment($appointment->id, 'نوبت تلفنی فعال و تأییدشده نیست.');

            return;
        }

        $activeRuleIds = [];
        foreach (ConsultationSmsReminderRule::where('active', true)->whereNotNull('template')->get() as $rule) {
            if (blank($rule->template)) {
                continue;
            }
            $activeRuleIds[] = $rule->id;
            $this->schedule($appointment, $rule);
        }

        ConsultationSmsDelivery::where('appointment_id', $appointment->id)
            ->whereNotNull('reminder_rule_id')
            ->when($activeRuleIds !== [], fn ($query) => $query->whereNotIn('reminder_rule_id', $activeRuleIds))
            ->whereIn('status', ['pending', 'queued', 'retrying'])
            ->update(['status' => 'skipped', 'error_message' => 'قانون یادآوری غیرفعال یا حذف شده است.']);
    }

    public function syncRule(ConsultationSmsReminderRule $rule): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        if (! $rule->active || blank($rule->template)) {
            $rule->deliveries()->whereIn('status', ['pending', 'queued', 'retrying'])
                ->update(['status' => 'skipped', 'error_message' => 'قانون یادآوری غیرفعال شده است.']);

            return;
        }

        $rule->deliveries()->where('recipient_type', '!=', $rule->recipient_type)
            ->whereIn('status', ['pending', 'queued', 'retrying'])
            ->update(['status' => 'skipped', 'error_message' => 'گیرنده قانون یادآوری تغییر کرده است.']);

        AppointmentUser::with(['user', 'doctor', 'service'])
            ->where('kind', AppointmentUserKindEnum::VOIP->value)
            ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
            ->where('date_visit', '>', now())
            ->chunkById(200, function ($appointments) use ($rule) {
                foreach ($appointments as $appointment) {
                    $this->schedule($appointment, $rule);
                }
            });
    }

    public function syncDueCandidates(): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        $maximumOffset = ConsultationSmsReminderRule::where('active', true)->max('minutes_before');
        if (! $maximumOffset) {
            return;
        }

        AppointmentUser::with(['user', 'doctor', 'service'])
            ->where('kind', AppointmentUserKindEnum::VOIP->value)
            ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
            ->where('date_visit', '>', now())
            ->where('date_visit', '<=', now()->addMinutes((int) $maximumOffset))
            ->chunkById(200, function ($appointments) {
                foreach ($appointments as $appointment) {
                    $this->syncAppointment($appointment);
                }
            });
    }

    public function dispatchDue(): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        ConsultationSmsDelivery::whereNotNull('reminder_rule_id')->where('status', 'queued')
            ->where('updated_at', '<=', now()->subMinutes(15))->update([
                'status' => 'pending', 'error_message' => 'ارسال به‌دلیل توقف صف دوباره در صف قرار گرفت.',
            ]);

        ConsultationSmsDelivery::whereNotNull('reminder_rule_id')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($deliveries) {
                foreach ($deliveries as $delivery) {
                    $claimed = ConsultationSmsDelivery::whereKey($delivery->id)
                        ->where('status', 'pending')
                        ->update(['status' => 'queued', 'error_message' => null]);
                    if (! $claimed) {
                        continue;
                    }
                    try {
                        SendConsultationSms::dispatch($delivery->id, tenant()?->getTenantKey());
                    } catch (\Throwable $exception) {
                        ConsultationSmsDelivery::whereKey($delivery->id)->where('status', 'queued')
                            ->update(['status' => 'pending', 'error_message' => mb_substr($exception->getMessage(), 0, 2000)]);
                        report($exception);
                    }
                }
            });
    }

    private function schedule(AppointmentUser $appointment, ConsultationSmsReminderRule $rule): void
    {
        $recipient = $rule->recipient_type === ConsultationSmsReminderRule::RECIPIENT_PATIENT
            ? $appointment->user?->mobile
            : $appointment->doctor?->mobile;
        $scheduledAt = $appointment->date_visit->copy()->subMinutes($rule->minutes_before);
        $key = implode(':', ['voip-reminder', $rule->id, $appointment->id, $rule->recipient_type]);
        $attributes = [
            'reminder_rule_id' => $rule->id,
            'appointment_id' => $appointment->id,
            'practitioner_id' => $appointment->doctor_id,
            'type' => 'voip_appointment_reminder',
            'recipient_type' => $rule->recipient_type,
            'rule_title' => $rule->title,
            'recipient' => $recipient ?: '',
            'template' => $rule->template,
            'scheduled_at' => $scheduledAt,
            'status' => blank($recipient) ? 'skipped' : 'pending',
            'attempts' => 0,
            'error_message' => blank($recipient) ? 'شماره موبایل گیرنده ثبت نشده است.' : null,
            'payload' => ['params' => $this->parameters($appointment), 'message_text' => $rule->message_text],
        ];
        $delivery = ConsultationSmsDelivery::firstOrCreate(['deduplication_key' => $key], $attributes);

        if ($delivery->exists && $delivery->status === 'sent') {
            return;
        }

        if (! $delivery->wasRecentlyCreated) {
            $delivery->fill($attributes)->save();
        }
    }

    private function parameters(AppointmentUser $appointment): array
    {
        return [
            $appointment->user?->first_name ?? ' ',
            $appointment->user?->last_name ?? ' ',
            $appointment->doctor?->fullName ?? ' ',
            $appointment->service?->title ?? ' ',
            verta($appointment->date_visit)->format('Y/m/d'),
            verta($appointment->date_visit)->format('H:i'),
            $appointment->shortLinkUrl(),
            $appointment->tracking_code ?: (string) $appointment->id,
            (string) $appointment->id,
        ];
    }

    private function schemaReady(): bool
    {
        try {
            return Schema::hasTable('consultation_sms_reminder_rules')
                && Schema::hasTable('consultation_sms_deliveries')
                && Schema::hasColumns('consultation_sms_deliveries', ['reminder_rule_id', 'recipient_type', 'rule_title']);
        } catch (\Throwable) {
            return false;
        }
    }
}
