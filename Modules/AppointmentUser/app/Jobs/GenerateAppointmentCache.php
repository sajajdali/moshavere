<?php

namespace Modules\AppointmentUser\App\Jobs;

use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Service\AppointmentUserService;

class GenerateAppointmentCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // Allow up to 5 minutes for job execution
    public $queue = 'low'; // Assign to low-priority queue

    protected AppointmentSetting $appointmentSetting;

    /**
     * Create a new job instance.
     *
     * @param  AppointmentSetting  $appointmentSetting
     * @return void
     */
    public function __construct(AppointmentSetting $appointmentSetting , $forceDelete = false)
    {
        $this->appointmentSetting = $appointmentSetting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Cache::forget('appointmentList.' . $this->appointmentSetting->id);
        Cache::rememberForever('appointmentList.' . $this->appointmentSetting->id, function () {
            $this->appointmentSetting->update(['updated_log_at' => now()]);
            return app(AppointmentUserService::class)->listAppointments($this->appointmentSetting);
        });
    }
}
