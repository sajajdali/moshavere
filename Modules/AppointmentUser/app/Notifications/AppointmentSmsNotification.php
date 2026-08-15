<?php

namespace Modules\AppointmentUser\app\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Stancl\Tenancy\Tenancy;


class AppointmentSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?string $tenantId = null;

    /**
     * @param string|null $template
     */
    public function __construct(public ?string $template)
    {
        if (tenant()) {
            $this->tenantId = tenant()->getTenantKey(); // ذخیره tenant جاری
        }
    }

    protected function initializeTenant(): void
    {
        if ($this->tenantId) {
            app(Tenancy::class)->initialize($this->tenantId);
        }
    }

    /**
     * Create a new notification instance.
     */


    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toSms($notifiable)
    {
        return $notifiable->user->mobile;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $this->initializeTenant(); // اگه نیاز به اتصال DB هست، این بمونه

        $doctorName = $notifiable->doctor?->full_name;
        $firstName = $notifiable->user?->first_name;
        $lastName = $notifiable->user?->last_name;
        $serviceName = $notifiable->service?->title;

        $link = tenant_url('/s/' . $notifiable->shortLink->link_code);

        $dateAppointment = dateFormatSimlpe($notifiable->date_visit);
        $hour = substr($notifiable->start_time, 0, -3);

        // ذخیره لاگ برای بررسی
//        $logPath = base_path('fake-sms-log.txt');
//        $timestamp = now()->toDateTimeString();

//        $logContent = "=== [{$timestamp}] ===\n";
//        $logContent .= "Tenant ID: " . ($this->tenantId ?? 'N/A') . "\n";
//        $logContent .= "Generated URL: " . $link . "\n";
//        $logContent .= "User: {$firstName} {$lastName}\n";
//        $logContent .= "--------------------------\n\n";
//        file_put_contents($logPath, $logContent, FILE_APPEND);
        return [
            'template' => $this->template,
            'receptor' => $notifiable->user->mobile,
            'params' => [
                $firstName ?? ' ',
                $lastName ?? ' ',
                $doctorName,
                $serviceName,
                $dateAppointment,
                $hour,
                $link,
                $notifiable->tracking_code,
                $notifiable->id
            ],
        ];
    }
}
