<?php

namespace Modules\AppointmentUser\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Modules\Reminder\Enum\ReminderParametersEnum;
use Modules\Reminder\app\Models\AppointmentReminder;
use Modules\User\Notifications\UserMessageNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsReminder;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;

class SendReminderscommand extends Command
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
        Log::info('sendReminder connsole has been called');
        // sms reminder
        AppointmentReminder::where('send_at', '<', \now()->subhours(4))
            ->delete();
        $reminders =  AppointmentReminder::where('type', '1')
            ->whereHas('appointmentUser', function ($q) {
                return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
            })->where('send_at', '<', now())
            ->get();
        Log::info($reminders->count() . ' reminders exists to send');
        if (isset($reminders) && $reminders->isNotEmpty()) {
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

        // notification reminder
        AppointmentReminder::where('type', '2')
            ->where('send_at', '<', \now()->subhours(4))
            ->delete();
        $notifReminders =  AppointmentReminder::where('type', '2')
            ->whereHas('appointmentUser', function ($q) {
                return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
            })->where('send_at', '<', now())
            ->get();
        if (isset($notifReminders) && $notifReminders->isNotEmpty()) {
            foreach ($notifReminders as $notifReminder) {
                if ($notifReminder->reminder->status == ReminderStatusEnum::NOTIFICATION && $notifReminder->reminder->active) {
                    $param =  $notifReminder->reminder->parameters;
                    if (isset($param)) {
                        $sendParameter = [];
                        foreach ($param as $p) {
                            $sendParameter[] = ReminderParametersEnum::tryFrom($p);
                        }
                    }
                    if (isset($sendParameter)) {
                        $assignEachParameter =  $this->findPrameterEnum($sendParameter, $notifReminder);
                    }
                    $edited_param = $this->changeSmsParameters($assignEachParameter);
                    $message = $this->replaceParam($notifReminder->reminder->body, $edited_param);
                    try {
                        $notifReminder->appointmentUser->user->notify(new UserMessageNotification(
                            title: "یادآوری",
                            excerpt: $message,
                            message: '',
                        ));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    $notifReminder->delete();
                }
            }
        }
    }
    private function findPrameterEnum($sendParameter, $notifReminder)
    {
        foreach ($sendParameter as $key => $eachPram) {
            $assignEachParameter[] =  match ($eachPram) {
                ReminderParametersEnum::FIRST_NAME      => $notifReminder->appointmentUser->user->first_name,
                ReminderParametersEnum::LAST_NAME       => $notifReminder->appointmentUser->user->last_name,
                ReminderParametersEnum::VISIT_DATE      => verta($notifReminder->appointmentUser->date_visit)->format('Y/m/d'),
                ReminderParametersEnum::VISIT_TIME      => verta($notifReminder->appointmentUser->date_visit)->format('H:i'),
                ReminderParametersEnum::SERVICE_NAME    => $notifReminder->appointmentUser->service->title,
                ReminderParametersEnum::DOCTOR_NAME     => $notifReminder->appointmentUser->doctor->fullName,
                ReminderParametersEnum::LINK            => url('/s/' . $notifReminder->appointmentUser->shortLink->link_code),
                default => '',
            };
        }
        return $assignEachParameter;
    }
    private function replaceParam(string $message, array $params)
    {
        $regex = '/%([^%]+)%/';
        if (preg_match_all($regex, $message, $matches, PREG_PATTERN_ORDER)) {
            foreach ($matches[0] as $word) {
                if (array_key_exists($word, $params)) {
                    $message = str_replace($word, $params[$word], $message);
                }
            }
        }
        return $message;
    }
    private function changeSmsParameters($params)
    {
        if (!is_array($params)) {
            return $params;
        }
        $paramNum = 1;
        $newParams = [];
        foreach ($params as $param) {
            $newParams['%param' . $paramNum . '%'] = $param;
            $paramNum++;
        }
        return $newParams;
    }
}
