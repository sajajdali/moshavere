<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Modules\AppointmentSetting\app\Console\RenewCacheCommand;
use Modules\AppointmentUser\app\Console\CheckAppointmentUserDedlineDateCommand;
use Modules\AppointmentUser\app\Console\CompeleteOnlineAppointment;
use Modules\AppointmentUser\app\Console\DisabledAwnsweredOnlineAppointmentCommand;
use Modules\AppointmentUser\app\Console\MakeCacheCommand;
use Modules\AppointmentUser\app\Console\SendReminderscommand;
use Modules\Front\app\Console\GenerateSitemapComman;
use Modules\MigrateOldData\App\Console\MigrateAllOrders;
use Modules\MigrateOldData\App\Console\MigrateAppointmentSetting;
use Modules\MigrateOldData\App\Console\MigrateAppointmentUserCommand;
use Modules\MigrateOldData\App\Console\MigratePlacesCommand;
use Modules\MigrateOldData\App\Console\MigratePlaceUsersCommand;
use Modules\MigrateOldData\App\Console\MigrateServiceseCommand;
use Modules\MigrateOldData\App\Console\MigrateServiceUserCommand;
use Modules\MigrateOldData\App\Console\MigrateSpecialiteiesCommand;
use Modules\MigrateOldData\App\Console\MigrateUserMetasCommand;
use Modules\MigrateOldData\App\Console\MigrateUsersCommand;
use Stancl\Tenancy\Facades\Tenancy;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        MakeCacheCommand::class,
        SendReminderscommand::class,
        RenewCacheCommand::class,
        DisabledAwnsweredOnlineAppointmentCommand::class,
        CheckAppointmentUserDedlineDateCommand::class,
        MigrateAllOrders::class,
        MigratePlacesCommand::class,
        MigratePlaceUsersCommand::class,
        MigrateServiceseCommand::class,
        MigrateServiceUserCommand::class,
        MigrateSpecialiteiesCommand::class,
        MigrateUsersCommand::class,
        MigrateAppointmentSetting::class,
        MigrateUserMetasCommand::class,
        MigrateAppointmentUserCommand::class,
        CompeleteOnlineAppointment::class,
        GenerateSitemapComman::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            tenancy()->runForMultiple(null, function ($tenant) {
                Artisan::call('appointment:check-deadLine');
                Artisan::call('appointment:sendReminders');
            });
        })->everyFifteenMinutes();

        $schedule->call(function () {
            tenancy()->runForMultiple(null, fn () => Artisan::call('consultation:dispatch-sms'));
        })->everyMinute()->withoutOverlapping();

        $schedule->call(function () {
            tenancy()->runForMultiple(null, function ($tenant) {

                Artisan::call('appointment:check-end-at');
            });
        })->hourly();

        $schedule->call(function () {
            tenancy()->runForMultiple(null, function ($tenant) {

                Artisan::call('appointmentSetting:renew-cache');
            });
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');

        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            $payload = $event->job->payload();

            if (isset($payload['tenant_id'])) {
                Tenancy::initialize($payload['tenant_id']);
            }
        });
    }
}
