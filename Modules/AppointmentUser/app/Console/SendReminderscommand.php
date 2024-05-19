<?php

namespace Modules\AppointmentUser\app\Console;

use Illuminate\Console\Command;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Symfony\Component\Console\Input\InputOption;
use Modules\Reminder\Enum\ReminderParametersEnum;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Reminder\app\Models\AppointmentReminder;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsReminder;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;

class sendReminderscommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointment:sendReminders';

    /**
     * The console command description.
     */
    protected $description = 'send reminders';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AppointmentReminder::where('type', '1')
            ->where('send_at', '<', \now()->subhours(4))
            ->delete();
        $reminders =  AppointmentReminder::where('type', '1')
            ->where('send_at', '<', now())
            ->get();
        foreach ($reminders as $reminder) {
            if ($reminder->reminder->status == ReminderStatusEnum::SMS && $reminder->reminder->active) {
                $param =  $reminder->reminder->parameters;
                if (isset($param)) {
                    $sendParameter = [];
                    foreach ($param as $p) {
                        $sendParameter[] = ReminderParametersEnum::tryFrom($p);
                    }
                }
                $reminder->appointmentUser->notify(new AppointmentSmsReminder($reminder->reminder->body, $sendParameter));
                $reminder->delete();
            }
        }
    }
}
