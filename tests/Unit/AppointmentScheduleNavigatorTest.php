<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Modules\AppointmentUser\Service\AppointmentScheduleNavigator;
use PHPUnit\Framework\TestCase;

class AppointmentScheduleNavigatorTest extends TestCase
{
    public function test_both_directions_skip_days_without_a_weekly_schedule(): void
    {
        $navigator = new AppointmentScheduleNavigator([1, 3], [], [], []);
        $selected = Carbon::parse('2027-01-10');

        $this->assertSame('2027-01-05', $navigator->adjacent($selected, -1)->toDateString());
        $this->assertSame('2027-01-12', $navigator->adjacent($selected, 1)->toDateString());
        $this->assertSame('2027-01-10', $selected->toDateString());
    }

    public function test_saturday_and_friday_use_the_attendance_weekday_numbering(): void
    {
        $navigator = new AppointmentScheduleNavigator([0, 6], [], [], []);

        $this->assertSame('2027-01-09', $navigator->adjacent(Carbon::parse('2027-01-10'), -1)->toDateString());
        $this->assertSame('2027-01-15', $navigator->adjacent(Carbon::parse('2027-01-10'), 1)->toDateString());
    }

    public function test_special_dates_are_considered_in_both_directions_even_beyond_sixty_days(): void
    {
        $navigator = new AppointmentScheduleNavigator([], ['2026-01-01' => true, '2028-01-01' => true], [], []);

        $this->assertSame('2026-01-01', $navigator->adjacent(Carbon::parse('2027-01-10'), -1)->toDateString());
        $this->assertSame('2028-01-01', $navigator->adjacent(Carbon::parse('2027-01-10'), 1)->toDateString());
    }

    public function test_absences_and_holidays_are_skipped_but_explicit_holiday_attendance_is_allowed(): void
    {
        $navigator = new AppointmentScheduleNavigator([1, 3], [], [
            ['start' => '2027-01-11', 'end' => '2027-04-10'],
        ], ['2027-04-11' => true, '2027-01-05' => true]);

        $this->assertSame('2027-04-13', $navigator->adjacent(Carbon::parse('2027-01-10'), 1)->toDateString());
        $this->assertSame('2027-01-10', $navigator->adjacent(Carbon::parse('2027-04-13'), -1)->toDateString());
        $this->assertSame('2027-01-03', $navigator->adjacent(Carbon::parse('2027-01-10'), -1)->toDateString());

        $navigator = new AppointmentScheduleNavigator([1, 3], ['2027-01-05' => true], [], ['2027-01-05' => true]);
        $this->assertSame('2027-01-05', $navigator->adjacent(Carbon::parse('2027-01-10'), -1)->toDateString());
    }

    public function test_an_inactive_special_date_overrides_weekly_attendance(): void
    {
        $navigator = new AppointmentScheduleNavigator([1, 3], ['2027-01-05' => false, '2027-01-12' => false], [], []);

        $this->assertSame('2027-01-03', $navigator->adjacent(Carbon::parse('2027-01-10'), -1)->toDateString());
        $this->assertSame('2027-01-17', $navigator->adjacent(Carbon::parse('2027-01-10'), 1)->toDateString());
    }

    public function test_empty_or_exhausted_schedules_terminate_without_a_date(): void
    {
        $navigator = new AppointmentScheduleNavigator([], [], [], []);
        $this->assertNull($navigator->adjacent(Carbon::parse('2027-01-10'), -1));
        $this->assertNull($navigator->adjacent(Carbon::parse('2027-01-10'), 1));

        $navigator = new AppointmentScheduleNavigator([1, 3], [], [], []);
        $this->assertNull($navigator->adjacent(Carbon::parse('2027-01-10'), 1, Carbon::parse('2027-01-10')));
        $this->assertSame('2027-01-10', $navigator->adjacent(Carbon::parse('2028-01-10'), -1, Carbon::parse('2027-01-10'))->toDateString());
    }
}
