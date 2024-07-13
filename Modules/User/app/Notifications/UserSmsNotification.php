<?php

namespace Modules\User\app\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\User\Entities\User;

class UserSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $template;

    /**
     * @param $template
     */
    public function __construct($template)
    {
        $this->template = $template;
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
        $loginTemplate = setting(SettingKeyEnum::SMS_API_LOGIN_TEMPLATE);
        $user = $notifiable;

        return [
            'template' => $loginTemplate,
            'receptor' => $notifiable->mobile,
            'params' => [
                $user
            ],
        ];
    }
}
