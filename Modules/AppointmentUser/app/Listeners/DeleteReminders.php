<?php

namespace Modules\AppointmentUser\app\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Reminder\app\Models\AppointmentReminder;

class DeleteReminders
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */

    public function handle($event): void
    {
        $reminders =  AppointmentReminder::where('appointment_user_id', $event->appointmentUser->id)->get();
        if ($reminders->isNotEmpty()) {
            $reminders->each(function ($reminder) {
                $reminder->delete();
            });
        }
    }
}
