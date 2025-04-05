<?php

namespace Modules\Chat\app\Events;

use Modules\Chat\app\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AdminAnswerChatEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Chat $chat)
    {
        //
    }
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chat->id);
    }
}
