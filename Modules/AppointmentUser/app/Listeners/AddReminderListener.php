<?php

namespace Modules\AppointmentUser\app\Listeners;
use Modules\Reminder\app\Models\Reminder;
use Modules\Reminder\app\Models\AppointmentReminder;

class AddReminderListener
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
        $reminders = collect();
        //get the reminder that set for all the sections
        $reminders = $reminders->merge(Reminder::whereNull('reminderable_id')->whereNull('doctors')->get());

        //check if for  doctor reminder
        $specific_doctor = Reminder::whereNull('reminderable_id')->whereJsonContains('doctors', (string)$event->appointmentUser->doctor_id)->get();
        if ($specific_doctor->isNotEmpty()) {
            $reminders = $reminders->merge($specific_doctor);
        }

        //check if for  service reminder
        if ($event->appointmentUser->service && $event->appointmentUser->service->reminder) {
            $specific_service = $event->appointmentUser->service->reminder()->whereJsonContains('doctors', (string)$event->appointmentUser->doctor_id)->get();
            if ($specific_service->isNotEmpty()) {
                $reminders = $reminders->merge($specific_service);
            }
        }
        if ($reminders->isNotEmpty()) {
            $reminders->each(function ($reminder) use($event)  {
                $detail = [
                    'mobile' => $event->appointmentUser->user->mobile,
                    'parameter' => $reminder->parameters,
                ];
                AppointmentReminder::create([
                    'appointment_user_id' => $event->appointmentUser->id,
                    'type' => $reminder->status,
                    'send_at' => $event->appointmentUser->date_visit->addDays($reminder->send_day)->addHours($reminder->send_time),
                    'details' => $detail,
                ]);
            });
        }
    }
}
