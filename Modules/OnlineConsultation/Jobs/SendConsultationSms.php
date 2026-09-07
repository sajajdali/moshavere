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

class SendConsultationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public int $deliveryId, public ?string $tenantId = null) {}

    public function handle(): void
    {
        if ($this->tenantId && ! tenancy()->initialized) {
            tenancy()->initialize($this->tenantId);
        }
        if (! ConsultationAccess::enabled()) {
            return;
        }
        $delivery = ConsultationSmsDelivery::with('appointment')->findOrFail($this->deliveryId);
        if ($delivery->status === 'sent') {
            return;
        }
        if ($delivery->appointment && ($delivery->appointment->kind === AppointmentUserKindEnum::IN_PERSION || in_array($delivery->appointment->status, [AppointmentUserStatusEnum::STATUS_CANCEL, AppointmentUserStatusEnum::STATUS_DISAPPROVED], true))) {
            $delivery->update(['status' => 'skipped', 'error_message' => 'نوبت فعال مشاوره نیست یا لغو شده است.']);

            return;
        }
        if (blank($delivery->recipient) || blank($delivery->template)) {
            $delivery->update(['status' => 'skipped', 'error_message' => 'شماره موبایل یا نام قالب تنظیم نشده است.']);

            return;
        }
        $delivery->increment('attempts');
        try {
            Notification::route('sms', $delivery->recipient)->notifyNow(new AutomaticConsultationSms($delivery->template, $delivery->recipient, $delivery->payload['params'] ?? []));
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
}
