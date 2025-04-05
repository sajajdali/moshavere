<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Front\app\Console\GenerateSitemapComman;
use Modules\MigrateOldData\App\Console\MigrateAllOrders;
use Modules\AppointmentUser\app\Console\MakeCacheCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\MigrateOldData\App\Console\MigrateUsersCommand;
use Modules\MigrateOldData\App\Console\MigratePlacesCommand;
use Modules\AppointmentSetting\app\Console\RenewCacheCommand;
use Modules\AppointmentUser\app\Console\SendReminderscommand;
use Modules\MigrateOldData\App\Console\MigrateServiceseCommand;
use Modules\MigrateOldData\App\Console\MigrateUserMetasCommand;
use Modules\MigrateOldData\App\Console\MigratePlaceUsersCommand;
use Modules\MigrateOldData\App\Console\MigrateAppointmentSetting;
use Modules\MigrateOldData\App\Console\MigrateServiceUserCommand;
use Modules\AppointmentUser\app\Console\CompeleteOnlineAppointment;
use Modules\MigrateOldData\App\Console\MigrateSpecialiteiesCommand;
use Modules\MigrateOldData\App\Console\MigrateAppointmentUserCommand;
use Modules\AppointmentUser\app\Console\CheckAppointmentUserDedlineDateCommand;
use Modules\AppointmentUser\app\Console\DisabledAwnsweredOnlineAppointmentCommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        MakeCacheCommand::class ,
        SendReminderscommand::class ,
        RenewCacheCommand::class ,
        DisabledAwnsweredOnlineAppointmentCommand::class ,
        CheckAppointmentUserDedlineDateCommand::class ,
        MigrateAllOrders::class ,
        MigratePlacesCommand::class ,
        MigratePlaceUsersCommand::class ,
        MigrateServiceseCommand::class ,
        MigrateServiceUserCommand::class ,
        MigrateSpecialiteiesCommand::class ,
        MigrateUsersCommand::class ,
        MigrateAppointmentSetting::class ,
        MigrateUserMetasCommand::class ,
        MigrateAppointmentUserCommand::class ,
        CompeleteOnlineAppointment::class,
        GenerateSitemapComman::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('appointment:check-deadLine')->hourly();
        $schedule->command('appointment:check-end-at')->hourly();
        $schedule->command('appointment:sendReminders')->everyFiveMinutes();
        $schedule->command('appointmentSetting:renew-cache')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
