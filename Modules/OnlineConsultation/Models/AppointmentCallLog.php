<?php

namespace Modules\OnlineConsultation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentCallLog extends Model
{
    public const EARLY_STATES = ['BEFORE_APPOINTMENT', 'WAITING_FOR_APPOINTMENT'];

    protected $table = 'appointment_call_logs';
    protected $guarded = ['id'];
    protected $casts = [
        'raw_payload' => 'array', 'additional_data' => 'array', 'attempts' => 'array',
        'appointment_start_at' => 'datetime', 'appointment_end_at' => 'datetime',
        'call_entered_at' => 'datetime', 'dial_started_at' => 'datetime',
        'answered_at' => 'datetime', 'ended_at' => 'datetime',
    ];
    protected $hidden = ['raw_payload'];
    public function appointment() { return $this->belongsTo(AppointmentUser::class, 'appointment_id'); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
    public function consultantHangup() { return $this->hasOne(AppointmentConsultantHangup::class, 'call_id', 'call_id'); }
    public function consultantNoAnswer() { return $this->hasOne(AppointmentConsultantNoAnswer::class, 'call_id', 'call_id'); }

    /**
     * Prefer the recorded timestamps over appointment_state. Some PBX reports have
     * historically sent IN_APPOINTMENT_TIME a few minutes before the actual start.
     */
    public function isEarlyCall(): bool
    {
        if ($this->call_entered_at && $this->appointment_start_at) {
            return $this->call_entered_at->lt($this->appointment_start_at);
        }

        return in_array($this->appointment_state, self::EARLY_STATES, true);
    }

    public function earlyBySeconds(): ?int
    {
        if (! $this->call_entered_at || ! $this->appointment_start_at || ! $this->call_entered_at->lt($this->appointment_start_at)) {
            return null;
        }

        return (int) ceil($this->call_entered_at->diffInSeconds($this->appointment_start_at));
    }

    public function earlyByMinutes(): ?int
    {
        $seconds = $this->earlyBySeconds();

        return $seconds === null ? null : intdiv($seconds, 60);
    }

    public function occurredDuringAppointment(): bool
    {
        if ($this->call_entered_at && $this->appointment_start_at) {
            if ($this->call_entered_at->lt($this->appointment_start_at)) {
                return false;
            }

            return ! $this->appointment_end_at || $this->call_entered_at->lte($this->appointment_end_at);
        }

        return $this->appointment_state === 'IN_APPOINTMENT_TIME';
    }

    public function countsAsUnanswered(): bool
    {
        return $this->final_result !== 'ANSWERED' && $this->occurredDuringAppointment();
    }

    public function wasDisconnectedByPatient(): bool
    {
        return $this->final_result === 'ANSWERED'
            && $this->disconnected_by === 'PATIENT'
            && $this->consultantHangup === null;
    }

    public function isShortTalk(int $thresholdSeconds): bool
    {
        return (int) $this->talk_duration_seconds <= max(0, $thresholdSeconds);
    }

    public function isConsultantHangupWarning(int $thresholdSeconds): bool
    {
        return $this->consultantHangup !== null
            && $this->occurredDuringAppointment()
            && $this->isShortTalk($thresholdSeconds);
    }

    public function isCompletedConsultantHangup(int $thresholdSeconds): bool
    {
        return $this->consultantHangup !== null
            && $this->occurredDuringAppointment()
            && ! $this->isShortTalk($thresholdSeconds);
    }

    public function surveyScore(): ?int
    {
        foreach ([
            data_get($this->additional_data, 'survey_score'),
            data_get($this->additional_data, 'score'),
            data_get($this->additional_data, 'rating'),
            data_get($this->raw_payload, 'survey_score'),
            data_get($this->raw_payload, 'score'),
            data_get($this->raw_payload, 'rating'),
        ] as $value) {
            if (is_numeric($value) && (int) $value >= 1 && (int) $value <= 5) {
                return (int) $value;
            }
        }

        return null;
    }

    public function scopeDuringAppointment(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereNotNull('call_entered_at')
                    ->whereNotNull('appointment_start_at')
                    ->whereColumn('call_entered_at', '>=', 'appointment_start_at')
                    ->where(function (Builder $query) {
                        $query->whereNull('appointment_end_at')
                            ->orWhereColumn('call_entered_at', '<=', 'appointment_end_at');
                    });
            })->orWhere(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNull('call_entered_at')->orWhereNull('appointment_start_at');
                })->where('appointment_state', 'IN_APPOINTMENT_TIME');
            });
        });
    }

    public function scopeEarly(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereNotNull('call_entered_at')
                    ->whereNotNull('appointment_start_at')
                    ->whereColumn('call_entered_at', '<', 'appointment_start_at');
            })->orWhere(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNull('call_entered_at')->orWhereNull('appointment_start_at');
                })->whereIn('appointment_state', self::EARLY_STATES);
            });
        });
    }
}
