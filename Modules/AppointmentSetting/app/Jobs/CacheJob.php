<?php

namespace Modules\AppointmentSetting\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class CacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AppointmentSetting $appointmentSetting;

    /**
     * @param AppointmentSetting $appointmentSetting
     */
    public function __construct(AppointmentSetting $appointmentSetting)
    {
        $this->appointmentSetting = $appointmentSetting;
    }

    /**
     * Create a new job instance.
     */


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheName = 'appointmentList.'.$this->appointmentSetting->id;
        Cache::forget($cacheName);
        Cache::rememberForever($cacheName, function () {
            return app('AppointmentUserService')->listAppointments($this->appointmentSetting);
        });
    }
}
