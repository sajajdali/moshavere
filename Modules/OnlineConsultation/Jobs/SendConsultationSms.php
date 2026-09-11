<?php

namespace Modules\OnlineConsultation\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Notifications\AutomaticConsultationSms;
use Modules\OnlineConsultation\Support\ConsultationAccess;
use Modules\OnlineConsultation\Services\ConsultationReminderScheduler;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;

class SendConsultationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public int $deliveryId, public ?string $tenantId = null) {}

    public function handle(ConsultationReminderScheduler $scheduler): void
    {
        if ($this->tenantId && ! tenancy()->initialized) {
            tenancy()->initialize($this->tenantId);
        }
        if (! ConsultationAccess::enabled()) {
            return;
        }
        $delivery = ConsultationSmsDelivery::with(['appointment', 'reminderRule'])->findOrFail($this->deliveryId);
        if (in_array($delivery->status, ['sent', 'skipped', 'failed'], true)) {
            return;
        }
        if (! $this->reminderIsReady($delivery, $scheduler)) {
            return;
        }
        if (blank($delivery->recipient) || blank($delivery->template)) {
            $delivery->update(['status' => 'skipped', 'error_message' => 'شماره موبایل یا نام قالب تنظیم نشده است.']);

            return;
        }
        if (filter_var(env('SMS_SEND_SANDBOX', false), FILTER_VALIDATE_BOOL)) {
            $delivery->update(['status' => 'skipped', 'error_message' => 'ارسال واقعی پیامک در محیط Sandbox غیرفعال است.']);

            return;
        }
        if (blank(Setting::v(SettingKeyEnum::SMS_API_TOKEN))) {
            $delivery->update(['status' => 'failed', 'error_message' => 'توکن API پنل پیامک تنظیم نشده است.']);

            return;
        }
        $delivery->refresh()->load(['appointment', 'reminderRule']);
        if (! $this->reminderIsReady($delivery, $scheduler)) {
            return;
        }
        $delivery->increment('attempts');
        try {
            Notification::route('sms', $delivery->recipient)->notifyNow(new AutomaticConsultationSms(
                $delivery->template,
                $delivery->recipient,
                $delivery->payload['params'] ?? [],
                $delivery->payload['message_text'] ?? null,
            ));
            $delivery->update(['status' => 'sent', 'sent_at' => now(), 'provider_response' => 'درخواست به کانال پیامک تحویل شد.', 'error_message' => null]);
        } catch (\Throwable $e) {
            $delivery->update(['status' => $this->attempts() >= $this->tries ? 'failed' : 'retrying', 'error_message' => mb_substr($e->getMessage(), 0, 2000)]);
            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->tenantId && ! tenancy()->initialized) {
            tenancy()->initialize($this->tenantId);
        }
        if (! ConsultationAccess::schemaReady(['consultation_sms_deliveries'])) {
            return;
        }
        ConsultationSmsDelivery::whereKey($this->deliveryId)->where('status', '!=', 'sent')->update([
            'status' => 'failed',
            'error_message' => mb_substr($exception?->getMessage() ?: 'ارسال پس از سه تلاش ناموفق بود.', 0, 2000),
        ]);
    }

    private function reminderIsReady(ConsultationSmsDelivery $delivery, ConsultationReminderScheduler $scheduler): bool
    {
        if (! $delivery->reminder_rule_id) {
            return true;
        }
        if (
            ! $delivery->reminderRule?->active
            || ! $delivery->appointment
            || $delivery->appointment->kind !== AppointmentUserKindEnum::VOIP
            || $delivery->appointment->status !== AppointmentUserStatusEnum::STATUS_SUCCESSFUL
            || ! $delivery->appointment->date_visit?->isFuture()
        ) {
            $delivery->update(['status' => 'skipped', 'error_message' => 'نوبت تلفنی فعال و تأییدشده نیست یا لغو/حذف شده است.']);

            return false;
        }

        $expectedAt = $delivery->appointment->date_visit->copy()->subMinutes($delivery->reminderRule->minutes_before);
        if (! $delivery->scheduled_at->equalTo($expectedAt)) {
            $scheduler->syncAppointment($delivery->appointment);

            return false;
        }
        if ($delivery->scheduled_at->isFuture()) {
            $delivery->update(['status' => 'pending', 'error_message' => null]);

            return false;
        }

        return true;
    }
}
