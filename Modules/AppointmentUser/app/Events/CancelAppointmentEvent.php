<?php

namespace Modules\AppointmentUser\app\Events;

use Illuminate\Queue\SerializesModels;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class CancelAppointmentEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public AppointmentUser $appointmentUser)
    {
        //
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
