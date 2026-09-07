<?php

namespace Modules\AppointmentUser\Service;

use Carbon\Carbon;

class AppointmentScheduleNavigator
{
    public function __construct(
        private array $weeklyDays,
        private array $specialDates,
        private array $absences,
        private array $holidays,
    ) {}

    public function adjacent(Carbon $selectedDate, int $direction, ?Carbon $lastDay = null): ?Carbon
    {
        $direction = $direction < 0 ? -1 : 1;
        $cursor = $selectedDate->copy()->startOfDay()->addDays($direction);
        if ($lastDay && $direction < 0) {
            $cursor = $cursor->min($lastDay->copy()->startOfDay());
        }

        while (!$lastDay || $cursor->lte($lastDay)) {
            $candidates = [];
            // Attendance settings number Saturday as 0 and Friday as 6.
            $weekday = ($cursor->dayOfWeek + 1) % 7;
            foreach ($this->weeklyDays as $day) {
                $offset = $direction > 0 ? ($day - $weekday + 7) % 7 : ($weekday - $day + 7) % 7;
                $candidates[] = $cursor->copy()->addDays($direction * $offset)->toDateString();
            }
            foreach ($this->specialDates as $date => $active) {
                if ($active && ($direction > 0 ? $date >= $cursor->toDateString() : $date <= $cursor->toDateString())) {
                    $candidates[] = $date;
                }
            }
            if (!$candidates) {
                return null;
            }
            $date = $direction > 0 ? min($candidates) : max($candidates);
            $candidate = Carbon::parse($date)->startOfDay();
            if ($lastDay && $candidate->gt($lastDay)) {
                return null;
            }

            foreach ($this->absences as $absence) {
                if ($date >= $absence['start'] && $date <= $absence['end']) {
                    $cursor = Carbon::parse($direction > 0 ? $absence['end'] : $absence['start'])->addDays($direction);
                    continue 2;
                }
            }
            // A special-date schedule overrides the weekly schedule, including on holidays.
            $isSpecial = array_key_exists($date, $this->specialDates);
            if (($isSpecial && !$this->specialDates[$date]) || (!$isSpecial && isset($this->holidays[$date]))) {
                $cursor = $candidate->addDays($direction);
                continue;
            }

            return $candidate;
        }

        return null;
    }
}
