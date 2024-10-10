<?php

namespace Modules\Api\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Setting\Enum\SettingKeyEnum;

class AuthSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public string $code,public int $type = 1 )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms($notifiable)
    {
        return $notifiable->mobile;
    }

    private function split_number_by_two($number): string
    {
        // Convert the number to a string
        $number = (string)$number;

        // Get the length of the number
        $length = strlen($number);

        // Split the number into groups of two digits
        $result = '';
        for ($i = 0; $i < $length; $i += 2) {
            if ($i + 2 > $length) {
                // If the last group has only one digit
                $result .= substr($number, $i, 1);
            } else {
                // Add two digits to the result
                $result .= substr($number, $i, 2) . ' ';
            }
        }

        // Trim any trailing space
        return trim($result);
    }
    /**
     * Get the array representation of the notification.
     */
    public function toArray(mixed $notifiable): array
    {
        $param = $this->code;
        $loginTemplate = setting(SettingKeyEnum::SMS_API_LOGIN_TEMPLATE);
        if($this->type ==  2 ){
            $loginTemplate = setting(SettingKeyEnum::CALL_LOGIN_TEMPLATE);
            $param = $this->split_number_by_two($loginTemplate);
        }
        return [
            'template' => $loginTemplate,
            'receptor' => $notifiable->mobile,
            'type'     => $this->type ,
            'params' => [
                $param,
            ],
        ];
    }
}
