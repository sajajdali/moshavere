<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\SpecificDayAvailableAppointment;
use Modules\AppointmentUser\Service\AppointmentUserService;
use Tests\TestCase;

class SpecificDayAppointmentDateSelectionTest extends TestCase
{
    private function appointmentComponent(): SpecificDayAvailableAppointment
    {
        $component = new SpecificDayAvailableAppointment();
        $component->fetchData['appointmentSetting'] = new AppointmentSetting();
        $component->fetchData['selectedDate'] = Carbon::parse('2026-09-07');
        $component->fetchData['listOfAppointment'] = [
            ['date' => '2026-09-07', 'times' => []],
            ['date' => '2026-11-06', 'times' => []],
        ];
        $component->fetchData['showingAppointmentIndex'] = 1;

        return $component;
    }

    private function expectSelectedDate(string $date, ?int $segmentTime = null, bool $empty = false): void
    {
        $details = ['specialDays' => $date];
        if ($segmentTime !== null) {
            $details['segment_time'] = $segmentTime;
        }
        $service = \Mockery::mock(AppointmentUserService::class);
        $service->shouldReceive('listAppointments')->once()
            ->with(\Mockery::type(AppointmentSetting::class), $details)
            ->andReturn([
                'report' => ['min_day_active' => 0],
                'data' => [1405 => [10 => [20 => [
                    'day_number_gmt' => $date,
                    'times' => $empty ? [] : [[
                        'status' => true,
                        'timestamp' => Carbon::parse($date)->setTime(19, 38)->timestamp,
                        'from' => '19:38:00',
                        'until' => '19:53:00',
                    ]],
                ]]]],
            ]);
        $this->app->instance('AppointmentUserService', $service);
    }

    public function test_picker_loads_a_date_beyond_the_existing_calendar(): void
    {
        $component = $this->appointmentComponent();
        $component->form['changeDate'] = '1405/10/20';
        $this->expectSelectedDate('2027-01-10');

        $component->loadDifferentDayDetail();

        $this->assertSame('2027-01-10', $component->fetchData['selectedDate']->toDateString());
        $this->assertSame('1405/10/20', $component->form['changeDate']);
        $this->assertSame('19:38:00', $component->ShowListOfAppointmentForSpecificDay()[0]['from']);
        $this->assertSame(0, $component->fetchData['showingAppointmentIndex']);
    }

    public function test_picker_preserves_segment_duration_when_loading_an_earlier_date(): void
    {
        $component = $this->appointmentComponent();
        $component->form['changeDate'] = '1405/06/01';
        $component->fetchData['segment_time'] = 30;
        $this->expectSelectedDate('2026-08-23', 30);

        $component->loadDifferentDayDetail();

        $this->assertSame('2026-08-23', $component->fetchData['selectedDate']->toDateString());
        $this->assertNotEmpty($component->ShowListOfAppointmentForSpecificDay());
    }

    public function test_empty_selected_day_clears_the_old_day_index(): void
    {
        $component = $this->appointmentComponent();
        $component->form['changeDate'] = '1405/10/20';
        $this->expectSelectedDate('2027-01-10', empty: true);

        $component->loadDifferentDayDetail();

        $this->assertSame([], $component->ShowListOfAppointmentForSpecificDay());
        $this->assertArrayNotHasKey('showingAppointmentIndex', $component->fetchData);
    }

    public function test_previous_and_next_load_the_nearest_scheduled_date_and_sync_the_picker(): void
    {
        $component = $this->appointmentComponent();
        $component->fetchData['selectedDate'] = Carbon::parse('2027-01-10');
        $component->form['changeDate'] = '1405/10/20';
        $this->expectSelectedDate('2027-01-05');
        app('AppointmentUserService')->shouldReceive('adjacentScheduledDay')->once()
            ->with(\Mockery::type(AppointmentSetting::class), \Mockery::on(fn($d) => $d->toDateString() === '2027-01-10'), -1)
            ->andReturn(Carbon::parse('2027-01-05'));

        $component->previousDay();

        $this->assertSame('1405/10/15', $component->form['changeDate']);
        $this->assertSame('19:38:00', $component->ShowListOfAppointmentForSpecificDay()[0]['from']);

        $this->expectSelectedDate('2027-01-10');
        app('AppointmentUserService')->shouldReceive('adjacentScheduledDay')->once()
            ->with(\Mockery::type(AppointmentSetting::class), \Mockery::on(fn($d) => $d->toDateString() === '2027-01-05'), 1)
            ->andReturn(Carbon::parse('2027-01-10'));

        $component->nextDay();

        $this->assertSame('1405/10/20', $component->form['changeDate']);
        $this->assertSame('19:38:00', $component->ShowListOfAppointmentForSpecificDay()[0]['from']);
    }

    public function test_navigation_keeps_the_selected_date_when_no_scheduled_day_exists(): void
    {
        $component = $this->appointmentComponent();
        $service = \Mockery::mock(AppointmentUserService::class);
        $service->shouldReceive('adjacentScheduledDay')->once()->andReturnNull();
        $service->shouldNotReceive('listAppointments');
        $this->app->instance('AppointmentUserService', $service);

        $component->nextDay();

        $this->assertSame('2026-09-07', $component->fetchData['selectedDate']->toDateString());
        $this->assertNotEmpty($component->fetchData['navigationMessage']);
    }
}
