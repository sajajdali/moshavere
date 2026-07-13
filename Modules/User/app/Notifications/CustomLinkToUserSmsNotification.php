<?php

namespace Modules\User\app\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CustomLinkToUserSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $receptor,
        private readonly string $template,
        private readonly string $siteTitle,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(mixed $notifiable): string
    {
        return $this->receptor;
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'template' => $this->template,
            'receptor' => $this->receptor,
            'params' => [
                $this->siteTitle,
            ],
        ];
    }
}
