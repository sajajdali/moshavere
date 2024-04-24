<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Modules\appointmentUser\app\Events\StoreAppointment;
use Modules\AppointmentUser\app\Events\CancelAppointment;
use Modules\AppointmentUser\app\Listeners\DeleteReminders;
use Modules\AppointmentUser\app\Events\StoreAppointmentEvent;
use Modules\AppointmentUser\app\Events\CancelAppointmentEvent;
use Modules\AppointmentUser\app\Events\DeleteAppointmentEvent;
use Modules\AppointmentUser\app\Listeners\AddReminderListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        StoreAppointmentEvent::class => [
            AddReminderListener::class ,
        ],
        DeleteAppointmentEvent::class => [
            DeleteReminders::class ,
        ],
        CancelAppointmentEvent::class => [
            DeleteReminders::class ,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
