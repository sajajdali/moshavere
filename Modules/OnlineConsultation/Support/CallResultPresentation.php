<?php

namespace Modules\OnlineConsultation\Support;

use Modules\OnlineConsultation\Models\AppointmentCallLog;

class CallResultPresentation
{
    public static function label(AppointmentCallLog $call, int $shortCallThresholdSeconds = 60): string
    {
        if ($call->isEarlyCall()) {
            $seconds = $call->earlyBySeconds();
            if ($seconds === null) {
                return 'تماس پیش از زمان نوبت گرفته شده';
            }

            $minutes = intdiv($seconds, 60);
            $remainingSeconds = $seconds % 60;
            $distance = $minutes ? $minutes.' دقیقه' : '';
            if ($remainingSeconds) {
                $distance .= ($distance ? ' و ' : '').$remainingSeconds.' ثانیه';
            }

            return 'تماس '.$distance.' زودتر از نوبت گرفته شده';
        }

        if ($call->isCompletedConsultantHangup($shortCallThresholdSeconds)) {
            return 'مشاوره انجام شد و تماس پایان یافت';
        }

        if ($call->isConsultantHangupWarning($shortCallThresholdSeconds)) {
            return 'تماس کوتاه؛ قطع توسط مشاور';
        }

        if ($call->wasDisconnectedByPatient()) {
            return 'مشاوره برقرار شد؛ تماس توسط بیمار پایان یافت';
        }

        if ($call->countsAsUnanswered()) {
            return 'تماس بی‌پاسخ در زمان نوبت';
        }

        return self::rawLabel($call->final_result);
    }

    public static function tone(AppointmentCallLog $call, int $shortCallThresholdSeconds = 60): string
    {
        if ($call->isEarlyCall()) {
            return 'info';
        }

        if ($call->isCompletedConsultantHangup($shortCallThresholdSeconds)) {
            return 'success';
        }

        if ($call->isConsultantHangupWarning($shortCallThresholdSeconds) || $call->countsAsUnanswered()) {
            return 'danger';
        }

        if ($call->wasDisconnectedByPatient()) {
            return 'success';
        }

        return self::rawTone($call->final_result);
    }

    public static function rawLabel(?string $result): string
    {
        return [
            'ANSWERED' => 'پاسخ داده‌شده',
            'NOANSWER' => 'بی‌پاسخ',
            'BUSY' => 'مشغول',
            'CALLER_ABANDONED' => 'پایان تماس قبل از اتصال',
            'FAILED' => 'ناموفق',
            'CHANUNAVAIL' => 'داخلی در دسترس نیست',
            'CONGESTION' => 'اختلال شبکه',
            'NOT_DIALED' => 'شماره‌گیری نشده',
            'MISSING_EXTENSION' => 'داخلی تعریف نشده',
        ][$result] ?? ($result ?: 'نامشخص');
    }

    public static function rawTone(?string $result): string
    {
        return match ($result) {
            'ANSWERED' => 'success',
            'NOANSWER' => 'danger',
            'BUSY', 'CALLER_ABANDONED' => 'warning',
            'FAILED', 'CHANUNAVAIL', 'CONGESTION', 'MISSING_EXTENSION' => 'purple',
            default => 'muted',
        };
    }
}
