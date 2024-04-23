<?php

namespace Modules\AppointmentUser\app\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Setting\Enum\SettingKeyEnum;

class AppointmentSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string|null $template
     */
    public function __construct(public ?string $template)
    {
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
        $doctorName = $notifiable->doctor?->full_name;
        $firstName = $notifiable->user?->first_name;
        $lastName = $notifiable->user?->last_name;
        $serviceName = $notifiable->service?->title;
        $dateAppointment = dateFormatSimlpe($notifiable->date_visit);
        $hour = substr($notifiable->start_time, 0, -3);
        return [
            'template' => $this->template,
            'receptor' => $notifiable->user->mobile,
            'params' => [
                $firstName,
                $lastName,
                $doctorName,
                $serviceName,
                $dateAppointment,
                $hour,
                'appointment.test',
                $notifiable->tracking_code
            ],
        ];
    }
}
