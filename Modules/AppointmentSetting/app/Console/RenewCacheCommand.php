<?php

namespace Modules\AppointmentSetting\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class RenewCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointmentSetting:renew-cache';

    /**
     * The console command description.
     */
    protected $description = 'update the appointmentSetting cache(log)';

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
        $appointmentSettings = AppointmentSetting::whereData('appointmentSetting', '<', \now()->subDays(5)->toDateString());
        $appointmentSettings->each(function ($appointmentSetting) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
            Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
                $appointmentSetting->update(['updated_log_at' => \now()]);
                return app('AppointmentUserService')->listAppointments($appointmentSetting);
            });
        });
        $this->info(count($appointmentSettings) . ' ' . 'appointment setting cache has been updated');
    }
}
