<?php

namespace Modules\AppointmentUser\app\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\SmsChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentDocAndOperatorNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $template, public string $mobile)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [SmsChannel::class];
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
        $link = url('/s/' . $notifiable->shortLink->link_code);
        $dateAppointment = dateFormatSimlpe($notifiable->date_visit);
        $hour = substr($notifiable->start_time, 0, -3);
        return [
            'template' => $this->template,
            'receptor' => $this->mobile,
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
