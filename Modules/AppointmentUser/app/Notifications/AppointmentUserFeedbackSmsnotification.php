<?php

namespace Modules\AppointmentUser\app\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\SmsChannel;
use Illuminate\Notifications\Notification;

class AppointmentUserFeedbackSmsnotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ?string $template , public ?string $link_code)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }
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
        $link = tenant_url('/s/' . $this->link_code);
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
                $link,
                $notifiable->tracking_code
            ],
        ];
    }
}
