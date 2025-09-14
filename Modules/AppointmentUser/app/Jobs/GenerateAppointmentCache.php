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
     * @param AppointmentSetting $appointmentSetting
     * @param string|null $specialDay
     */
    public function __construct(
        protected AppointmentSetting $appointmentSetting,
        protected ?string $specialDay = null,
        protected mixed $segment = null,
    ) {
        $this->segment ??= null;
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (config('app.without_cache')) {
            return ; 
        }
        try {
            $cacheKey = 'appointmentList.' . $this->appointmentSetting->id;
            if (! is_null($this->segment)) {
                if ($this->appointmentSetting->segments()->exists()) {
                    $cacheKey = 'appointmentList.' . $this->appointmentSetting->id . '-' . $this->segment;
                }
            }
            if ($this->specialDay) {
                // If a specific day is given, fetch appointments only for that day
                $newOneDayData = app('AppointmentUserService')->listAppointments($this->appointmentSetting, [
                    'oneDay' => $this->specialDay,
                ]);

                if (Cache::has($cacheKey)) {
                    $cachedData = Cache::get($cacheKey);

                    // Replace the specific day data inside the cached structure
                    if (isset($newOneDayData['data'])) {
                        foreach ($newOneDayData['data'] as $year => $months) {
                            foreach ($months as $month => $days) {
                                foreach ($days as $day => $dayData) {
                                    $cachedData['data'][$year][$month][$day] = $dayData;
                                }
                            }
                        }
                        // Update cache with modified day
                        Cache::forever($cacheKey, $cachedData);
                    }
                } else {
                    // If cache doesn't exist, generate full cache
                    $fullData = app('AppointmentUserService')->listAppointments($this->appointmentSetting);
                    Cache::forever($cacheKey, $fullData);
                }
            } else {
                // If no specific day is provided, generate full cache directly
                $fullData = app('AppointmentUserService')->listAppointments($this->appointmentSetting);
                Cache::forever($cacheKey, $fullData);
            }
        } catch (\Exception $ex) {
            Log::info('GenerateAppointmentCache Error: ' . $ex->getMessage() . ' at ' . Carbon::now());
        }
    }

    // public function handle()
    // {
    //     try {
    //         Cache::forget('appointmentList.' . $this->appointmentSetting->id);
    //         Cache::rememberForever('appointmentList.' . $this->appointmentSetting->id, function () {
    //             $this->appointmentSetting->update(['updated_log_at' => now()]);
    //             return app(AppointmentUserService::class)->listAppointments($this->appointmentSetting);
    //         });
    //     } catch (\Exception $ex) {
    //         Log::info('GenerateAppointmentCache Error:  ' . $ex->getMessage() . ' time: ' . Carbon::now());
    //     }
    // }
}
