<?php

namespace Modules\AppointmentUser\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Enum\SettingKeyEnum;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

class CheckAppointmentUserDedlineDateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointment:check-deadLine';

    /**
     * The console command description.
     */
    protected $description = 'check if appointmentuser deadline is pass , make them unavaialble ';

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

        $appointmentsToDelete = AppointmentUser::whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<', \now())
            ->get();
        if ($appointmentsToDelete->isNotEmpty()) {
            $appointmentsToDelete->each(function ($appointment) {
                if ($appointment->status == AppointmentUserStatusEnum::STATUS_PENDING) {
                    $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT);
                    if (isset($smsTemplate)) {
                        $appointment->notify(new AppointmentSmsNotification($smsTemplate));
                    }
                }
                $appointment->delete();
            });
            Log::info($appointmentsToDelete->count() . 'appointmentUser has been deleted');
        } else {
            Log::info('no appointment with deadline to delete');
        }
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
