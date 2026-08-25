<?php

namespace Modules\Reminder\app\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Reminder\app\Models\Reminder;
use Modules\Reminder\app\Models\AppointmentReminder;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;

class RegenerateAppointmentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function __construct(protected int $reminderId)
    {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        try {
            $reminder = Reminder::find($this->reminderId);
            if (!$reminder) {
                return;
            }

            AppointmentReminder::where('reminder_id', $reminder->id)->delete();

            if (!$reminder->active) {
                return;
            }

            $doctors = $reminder->doctors;

            AppointmentUser::query()
                ->where('kind', AppointmentUserKindEnum::IN_PERSION)
                ->where('date_visit', '>', now())
                ->when(!empty($doctors), function ($q) use ($doctors) {
                    $q->whereIn('doctor_id', $doctors);
                })
                ->when(
                    $reminder->reminderable_type && $reminder->reminderable_id,
                    function ($q) use ($reminder) {
                        $q->where('service_id', $reminder->reminderable_id);
                    }
                )
                ->with('user')
                ->chunkById(200, function ($appointmentUsers) use ($reminder) {
                    foreach ($appointmentUsers as $appointmentUser) {
                        AppointmentReminder::create([
                            'appointment_user_id' => $appointmentUser->id,
                            'reminder_id' => $reminder->id,
                            'type' => $reminder->status,
                            'send_at' => $appointmentUser->date_visit->subDays($reminder->send_day)->subHours($reminder->send_time),
                            'details' => [
                                'mobile' => $appointmentUser->user->mobile,
                                'parameter' => $reminder->parameters,
                            ],
                        ]);
                    }
                });
        } catch (\Throwable $ex) {
            Log::info('RegenerateAppointmentRemindersJob Error: ' . $ex->getMessage() . ' at ' . Carbon::now());
        }
    }
}
