<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;

class UserMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public string $title, public string $excerpt, public string $message,public mixed $params = null , public ?string $link = null)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     */
    public function via($notifiable): array
    {
        if (method_exists($notifiable,
                'routeNotificationForFcm') && is_array($notifiable->routeNotificationForFcm()) && count($notifiable->routeNotificationForFcm()) > 0) {
            return ['database', FcmChannel::class];
        }
        return ['database'];
    }

    public function toFcm($notifiable)
    {
        $params = is_array($this->params) ? $this->params : [];
        return FcmMessage::create()
            ->data(array_merge([
                'title' => $this->title,
                'excerpt' => $this->excerpt,
                'message' => $this->message,
                'link' => (string) $this->link,
            ], collect($params)->mapWithKeys(fn ($value, $key) => [
                (string) $key => is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            ])->all()))
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'message',
                    ],
                ],
            ])
            ->notification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->title($this->title)
                ->body($this->excerpt)
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'message' => $this->message,
            'params' => $this->params,
            'link' => $this->link,
        ];
    }
}
