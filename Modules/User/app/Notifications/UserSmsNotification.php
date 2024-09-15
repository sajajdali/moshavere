<?php

namespace Modules\User\app\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;

class UserSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $template,public string $link)
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
        return $notifiable->mobile;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(mixed $notifiable): array
    {


        return [
            'template' => $this->template,
            'receptor' => $notifiable->mobile,
            'params' => [
                $notifiable->full_name,
                $this->link ,
            ],
        ];
    }
}
