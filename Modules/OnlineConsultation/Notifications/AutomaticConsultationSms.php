<?php

namespace Modules\OnlineConsultation\Notifications;

use App\Broadcasting\SmsChannel;
use Illuminate\Notifications\Notification;

class AutomaticConsultationSms extends Notification
{
    public function __construct(
        private readonly string $template,
        private readonly string $recipient,
        private readonly array $params,
        private readonly ?string $message = null,
    ) {}

    public function via(): array
    {
        return [SmsChannel::class];
    }

    public function toArray(): array
    {
        return ['template' => $this->template, 'receptor' => $this->recipient, 'params' => $this->params, 'message' => $this->message];
    }
}
