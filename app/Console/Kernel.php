<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Modules\AppointmentUser\app\Console\MakeCacheCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\AppointmentSetting\app\Console\RenewCacheCommand;
use Modules\AppointmentUser\app\Console\SendReminderscommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        MakeCacheCommand::class ,
        SendReminderscommand::class ,
        RenewCacheCommand::class ,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('appointment:check-deadLine')->hourly();
        $schedule->command('appointment:appointment:sendReminders')->everyFifteenMinutes();
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
