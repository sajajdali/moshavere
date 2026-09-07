<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Service\AppointmentUserService;
use PHPUnit\Framework\TestCase;

class NearestAvailableAppointmentDaysTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function service(array $dates): AppointmentUserService
    {
        Carbon::setTestNow('2026-09-07 12:00:00');

        return new class($dates) extends AppointmentUserService {
            public array $requests = [];

            public function __construct(private array $dates)
            {
                parent::__construct(new \stdClass());
            }

            protected function nearestAppointmentSearchEnd(AppointmentSetting $setting, int $limit): Carbon
            {
                return $setting->last_day_active
                    ? parent::nearestAppointmentSearchEnd($setting, $limit)
                    : Carbon::today()->addYears(2);
            }

            public function listAppointments(AppointmentSetting $appointmentSetting, array $details = [])
            {
                $this->requests[] = $details;
                $data = [];
                foreach ($this->dates as $date) {
                    if ($date < $details['range_start'] || $date > $details['range_end']) {
                        continue;
                    }
                    $data[1][1][] = [
                        'status' => true,
                        'empty_appoints' => 1,
                        'day_number_gmt' => $date,
                        'times' => [[
                            'status' => true,
                            'timestamp' => Carbon::parse($date)->setTime(10, 0)->timestamp,
                            'from' => '10:00:00',
                            'until' => '10:15:00',
                        ]],
                    ];
                }

                return ['data' => $data];
            }
        };
    }

    public function test_it_finds_six_dates_beyond_sixty_days_and_passes_the_segment_duration(): void
    {
        $dates = ['2027-01-10', '2027-01-17', '2027-01-24', '2027-01-31', '2027-02-07', '2027-02-14', '2027-02-21'];
        $service = $this->service($dates);

        $result = $service->nearestAvailableDays(new AppointmentSetting(), ['segment_time' => 30]);

        $this->assertSame(array_slice($dates, 0, 6), array_keys($result));
        foreach ($service->requests as $index => $request) {
            $this->assertSame(30, $request['segment_time']);
            if ($index > 0) {
                $this->assertSame(
                    Carbon::parse($service->requests[$index - 1]['range_end'])->addDay()->toDateString(),
                    $request['range_start']
                );
            }
        }
    }

    public function test_it_does_not_drop_or_repeat_dates_at_a_batch_boundary(): void
    {
        $dates = ['2026-11-06', '2026-11-07', '2026-11-08', '2026-11-09', '2026-11-10', '2026-11-11'];
        $service = $this->service($dates);

        $this->assertSame($dates, array_keys($service->nearestAvailableDays(new AppointmentSetting())));
    }

    public function test_it_stops_at_the_schedule_end_even_with_fewer_than_six_dates(): void
    {
        $service = $this->service(['2027-01-10', '2027-01-17']);
        $setting = new AppointmentSetting();
        $setting->setDateFormat('Y-m-d H:i:s');
        $setting->last_day_active = '2027-01-10';

        $this->assertSame(['2027-01-10'], array_keys($service->nearestAvailableDays($setting)));
        $this->assertSame('2027-01-10', end($service->requests)['range_end']);
    }

    public function test_it_finishes_when_no_available_dates_exist(): void
    {
        $service = $this->service([]);

        $this->assertSame([], $service->nearestAvailableDays(new AppointmentSetting()));
        $this->assertSame('2028-09-07', end($service->requests)['range_end']);
    }

    public function test_it_skips_slots_that_have_already_passed_today(): void
    {
        $service = $this->service(['2026-09-07', '2026-09-08']);

        $this->assertSame(['2026-09-08'], array_keys($service->nearestAvailableDays(new AppointmentSetting())));
    }
}
