<?php

namespace Modules\AppointmentUser\app\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use App\Broadcasting\SmsChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Reminder\Enum\ReminderParametersEnum;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentSmsReminder extends Notification
{
    use Queueable;

    /**
     * @param string|null $template
     */
    public function __construct(public ?string $template, public ?array $params)
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

  $assignEachParameter = [];
   if( isset($this->params) ){
        foreach ($this->params as $key => $eachPram) {
           $assignEachParameter[]=  match ($eachPram) {
                ReminderParametersEnum::FIRST_NAME      => $notifiable->user->first_name ,
                ReminderParametersEnum::LAST_NAME       => $notifiable->user->last_name ,
                ReminderParametersEnum::VISIT_DATE      => verta($notifiable->date_visit)->format('Y/m/d') ,
                ReminderParametersEnum::VISIT_TIME      => verta($notifiable->date_visit)->format('H:i') ,
                ReminderParametersEnum::SERVICE_NAME    => $notifiable->service->title ,
                ReminderParametersEnum::DOCTOR_NAME     => $notifiable->doctor->fullName ,
                ReminderParametersEnum::LINK            => url('/s/' . $notifiable->shortLink->link_code) ,
                default => '',
            };
        }
    }

        return [
            'template' => $this->template,
            'receptor' => $notifiable->user->mobile,
            'params' => $assignEachParameter,
        ];
    }
}
