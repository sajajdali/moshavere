<?php

namespace Modules\AppointmentUser\app\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointment:regenerate_cache';

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

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
        $oneDayAgo = Carbon::now()->subDay();
        $appointmentSettings = AppointmentSetting::whereNull('updated_log_at')->orWhereDate('updated_log_at', '<', $oneDayAgo);
       foreach ($appointmentSettings as $appointmentSetting){
           $cacheName = 'appointmentList.'.$appointmentSetting->id;
           Cache::forget($cacheName);
           Cache::rememberForever($cacheName, function () use ($appointmentSetting) {
               return app('AppointmentUserService')->listAppointments($appointmentSetting);
           });
           $appointmentSetting->update([
               'updated_log_at' => Carbon::now()
           ]);
       }
    }

}
