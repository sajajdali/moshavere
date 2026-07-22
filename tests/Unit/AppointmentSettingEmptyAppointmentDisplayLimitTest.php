<?php

namespace Tests\Unit;

use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use PHPUnit\Framework\TestCase;

class AppointmentSettingEmptyAppointmentDisplayLimitTest extends TestCase
{
    public function test_it_returns_the_configured_positive_limit(): void
    {
        $setting = new AppointmentSetting();
        $setting->detail = [
            AppointmentSetting::MAX_EMPTY_APPOINTMENTS_SHOWN_PER_DAY => '4',
        ];

        $this->assertSame(4, $setting->emptyAppointmentDisplayLimit());
    }

    public function test_it_treats_an_empty_or_invalid_limit_as_disabled(): void
    {
        $setting = new AppointmentSetting();

        foreach ([null, 0, -1, 'invalid'] as $limit) {
            $setting->detail = [
                AppointmentSetting::MAX_EMPTY_APPOINTMENTS_SHOWN_PER_DAY => $limit,
            ];

            $this->assertNull($setting->emptyAppointmentDisplayLimit());
        }
    }
}
