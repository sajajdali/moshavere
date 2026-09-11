<?php

namespace Modules\AppointmentUser\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        $appointmentIds = AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT->value)
            ->where('details->payment->status', true)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', \now())
            ->pluck('id');

        $deletedCount = 0;
        foreach ($appointmentIds as $appointmentId) {
            $appointment = DB::transaction(function () use ($appointmentId) {
                $appointment = AppointmentUser::whereKey($appointmentId)
                    ->where('status', AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT->value)
                    ->where('details->payment->status', true)
                    ->whereNotNull('deadline_at')
                    ->where('deadline_at', '<', \now())
                    ->lockForUpdate()
                    ->first();

                if (! $appointment) {
                    return null;
                }

                $appointment->loadMissing('setting');
                $appointment->delete();

                return $appointment;
            });

            if (! $appointment) {
                continue;
            }

            $deletedCount++;
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_REMOVAL_WHEN_NON_PAYMENT);
            if (isset($smsTemplate)) {
                try {
                    $appointment->notify(new AppointmentSmsNotification($smsTemplate));
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
            if ($appointment->setting && $appointment->date_visit) {
                $appointment->setting->runGenerateCacheJob(specialDayConvert($appointment->date_visit));
            }
            Log::info($appointment->id . ' has been deleted after its payment deadline expired');
        }

        if ($deletedCount > 0) {
            Log::info($deletedCount . ' appointmentUsers have been deleted after payment expiration');
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
