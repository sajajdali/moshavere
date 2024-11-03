<?php

namespace Modules\AppointmentUser\app\Jobs;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\AppointmentUser\Service\AppointmentUserService;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

class GenerateAppointmentCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // Allow up to 5 minutes for job execution
    /**
     * Create a new job instance.
     *
     * @param  AppointmentSetting  $appointmentSetting
     * @return void
     */
    public function __construct(protected AppointmentSetting $appointmentSetting , $forceDelete = false)
    {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Cache::forget('appointmentList.' . $this->appointmentSetting->id);
            Cache::rememberForever('appointmentList.' . $this->appointmentSetting->id, function () {
                $this->appointmentSetting->update(['updated_log_at' => now()]);
                return app(AppointmentUserService::class)->listAppointments($this->appointmentSetting);
            });
        } catch (\Exception $ex) {
            Log::info('GenerateAppointmentCache Error:  ' . $ex->getMessage() . ' time: ' . Carbon::now() );
        }
    }
}
