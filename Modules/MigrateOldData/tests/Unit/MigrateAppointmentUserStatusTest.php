<?php

namespace Modules\MigrateOldData\Tests\Unit;

use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\MigrateOldData\App\Console\MigrateAppointmentUserCommand;
use PHPUnit\Framework\TestCase;

class MigrateAppointmentUserStatusTest extends TestCase
{
    /** @dataProvider legacyStatusProvider */
    public function test_it_maps_legacy_appointment_statuses(int $legacy, int $expected): void
    {
        $command = new MigrateAppointmentUserCommand();

        $this->assertSame($expected, $command->mapLegacyStatus($legacy));
    }

    public static function legacyStatusProvider(): array
    {
        return [
            'pending' => [0, AppointmentUserStatusEnum::STATUS_PENDING->value],
            'confirmed' => [1, AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value],
            'cancelled' => [2, AppointmentUserStatusEnum::STATUS_CANCEL->value],
        ];
    }
}
