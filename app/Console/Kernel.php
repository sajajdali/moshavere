<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\AppointmentUser\app\Console\MakeCacheCommand;
use Modules\AppointmentUser\app\Console\sendReminderscommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        MakeCacheCommand::class ,
        sendReminderscommand::class ,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('appointment:check-deadLine')->hourly();
        $schedule->command('appointment:appointment:sendReminders')->everyFifteenMinutes();
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
