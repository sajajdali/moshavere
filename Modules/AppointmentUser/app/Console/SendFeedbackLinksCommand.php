<?php

namespace Modules\AppointmentUser\app\Console;

use App\Models\ShortLink;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\FeedbackForm;
use Modules\AppointmentUser\app\Services\FeedbackFormResolver;
use Modules\AppointmentUser\app\Notifications\AppointmentUserFeedbackSmsnotification;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\Setting\Enum\SettingKeyEnum;

class SendFeedbackLinksCommand extends Command
{
    protected $signature = 'appointment:send-feedback-links
                            {--delay=10 : minutes to wait after the visit has finished}
                            {--max-age=3 : only appointments that finished in the last N hours (so old ones are never sent)}
                            {--dry-run : list the appointments without sending anything}
                            {--appointment= : send only to this appointment id, ignoring the delay / max-age window and the already-sent check}';

    protected $description = 'send the feedback short link to patients some minutes after their visit has finished';

    public function handle()
    {
        $smsTemplate = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (empty($smsTemplate)) {
            $this->warn('SMS_FEEDBACK template is not set, nothing to send');
            return self::SUCCESS;
        }

        $delay  = max(0, (int) $this->option('delay'));
        $maxAge = max(1, (int) $this->option('max-age'));
        $dryRun = (bool) $this->option('dry-run');
        $now    = now();
        // manual send for one appointment: the time window and the already-sent check do not apply
        $onlyId = $this->option('appointment') ? (int) $this->option('appointment') : null;

        $appointments = AppointmentUser::query()
            // the setting may have been deleted after the appointment was booked: its visit duration is still needed
            ->with(['setting' => fn ($query) => $query->withTrashed(), 'user'])
            ->when($onlyId, function ($query) use ($onlyId) {
                // a manual send only needs an appointment the patient can actually give feedback for
                $query->whereKey($onlyId)->whereIn('status', [
                    AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                    AppointmentUserStatusEnum::STATUS_ATTENDED,
                    AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED,
                ]);
            }, function ($query) use ($now, $maxAge) {
                $query->where('kind', AppointmentUserKindEnum::IN_PERSION)
                    ->where('type', AppointmentUserTypeEnum::MAIN__APPOINTMENT)
                    ->whereIn('status', [
                        AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                        AppointmentUserStatusEnum::STATUS_ATTENDED,
                    ])
                    // only recent visits (the visit day is today, or yesterday for visits that finish around midnight)
                    ->whereDate('date_visit', '>=', $now->copy()->subHours($maxAge)->toDateString())
                    ->whereDate('date_visit', '<=', $now->toDateString())
                    // the feedback link has not been sent before (the link is stored with the 'feedBack' type)
                    ->whereNotExists(function ($query) {
                        $query->selectRaw('1')
                            ->from('short_links')
                            ->whereColumn('short_links.shortlinkable_id', 'appointment_users.id')
                            ->where('short_links.shortlinkable_type', 'feedBack');
                    });
            })
            // an appointment that already has an answer never gets the link again
            ->whereDoesntHave('feedbacks')
            ->whereDoesntHave('feedbackAnswers')
            ->get();

        if ($onlyId && $appointments->isEmpty()) {
            $this->warn("appointment #{$onlyId} not found, not in a status that accepts feedback, or already answered");
            return self::SUCCESS;
        }

        $forms = FeedbackForm::query()->where('active', true)->get();

        $sent = 0;
        foreach ($appointments as $appointment) {
            $endsAt = $this->visitEndsAt($appointment);
            if (! $endsAt && ! $onlyId) {
                continue;
            }
            if (! $onlyId) {
                // the visit has finished and `delay` minutes have passed
                if ($now->lt($endsAt->copy()->addMinutes($delay))) {
                    continue;
                }
                if ($endsAt->lt($now->copy()->subHours($maxAge))) {
                    continue;
                }
            }
            if (empty($appointment->user?->mobile)) {
                continue;
            }

            $form = app(FeedbackFormResolver::class)->resolve($appointment, $forms);
            if (! $form) {
                $this->line("#{$appointment->id} has no matching feedback form, skipped");
                continue;
            }

            if ($dryRun) {
                $ended = $endsAt?->format('Y-m-d H:i') ?? 'unknown';
                $this->line("#{$appointment->id} visit ended {$ended} -> would send form #{$form->id} ({$form->title})");
                $sent++;
                continue;
            }

            if ($this->sendLink($appointment, $smsTemplate, $form)) {
                $sent++;
            }
        }

        $this->info(($dryRun ? 'would send ' : 'sent ') . $sent . ' feedback link(s)');

        return self::SUCCESS;
    }

    /**
     * When the visit is over: the stored end time of the appointment, otherwise its start plus
     * the visit duration of the appointment setting.
     */
    private function visitEndsAt(AppointmentUser $appointment): ?Carbon
    {
        if (empty($appointment->start_time)) {
            return null;
        }
        $day = $appointment->date_visit->toDateString();

        if (! empty($appointment->end_time)) {
            $end = Carbon::parse($day . ' ' . $appointment->end_time);
            if ($end->gt(Carbon::parse($day . ' ' . $appointment->start_time))) {
                return $end;
            }
        }

        $minutes = (int) $appointment->setting?->time_for_visit;
        if ($minutes <= 0) {
            return null;
        }

        return Carbon::parse($day . ' ' . $appointment->start_time)->addMinutes($minutes);
    }

    private function sendLink(AppointmentUser $appointment, string $smsTemplate, FeedbackForm $form): bool
    {
        // the link is created first so a second run can never send it again
        $shortLink = ShortLink::create([
            'link_code'          => ShortLink::generateShortLinkCode(),
            'link_url'           => $appointment->feedbackUrl($form->id),
            'shortlinkable_type' => 'feedBack',
            'shortlinkable_id'   => $appointment->id,
        ]);

        try {
            $appointment->notify(new AppointmentUserFeedbackSmsnotification($smsTemplate, $shortLink->link_code));
        } catch (\Throwable $e) {
            // not delivered: remove the link so the next run tries again
            $shortLink->delete();
            Log::error('feedback link sms failed for appointment ' . $appointment->id . ': ' . $e->getMessage());
            $this->error("#{$appointment->id} failed: {$e->getMessage()}");

            return false;
        }

        $this->line("#{$appointment->id} feedback link sent (form #{$form->id})");

        return true;
    }
}
