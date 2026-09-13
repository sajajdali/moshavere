<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal\SpecificDayAppointmentRegistrationModal;

class AppointmentRegistrationKindSelectionTest extends TestCase
{
    public function test_it_normalizes_persian_and_arabic_mobile_digits(): void
    {
        $this->assertSame(
            '09123456789',
            SpecificDayAppointmentRegistrationModal::normalizeMobileNumber('۰۹۱۲-٣٤٥ ۶۷۸۹')
        );
    }

    public function test_it_resolves_every_enabled_appointment_kind(): void
    {
        $kinds = SpecificDayAppointmentRegistrationModal::resolveAvailableKinds([
            AppointmentSetting::VISIT_TYPE_INPERSON => true,
            AppointmentSetting::VISIT_TYPE_VOIP => true,
            AppointmentSetting::VISIT_TYPE_ONLINE => true,
        ]);

        $this->assertSame([
            AppointmentUserKindEnum::IN_PERSION,
            AppointmentUserKindEnum::VOIP,
            AppointmentUserKindEnum::ONLINE,
        ], $kinds);
    }

    public function test_it_only_returns_the_single_enabled_kind(): void
    {
        $kinds = SpecificDayAppointmentRegistrationModal::resolveAvailableKinds([
            AppointmentSetting::VISIT_TYPE_INPERSON => false,
            AppointmentSetting::VISIT_TYPE_VOIP => true,
            AppointmentSetting::VISIT_TYPE_ONLINE => false,
        ]);

        $this->assertSame([AppointmentUserKindEnum::VOIP], $kinds);
    }

    public function test_it_resolves_in_person_and_voip_without_adding_online(): void
    {
        $kinds = SpecificDayAppointmentRegistrationModal::resolveAvailableKinds([
            AppointmentSetting::VISIT_TYPE_INPERSON => true,
            AppointmentSetting::VISIT_TYPE_VOIP => true,
            AppointmentSetting::VISIT_TYPE_ONLINE => false,
        ]);

        $this->assertSame([
            AppointmentUserKindEnum::IN_PERSION,
            AppointmentUserKindEnum::VOIP,
        ], $kinds);
    }

    public function test_string_false_values_are_not_treated_as_enabled(): void
    {
        $kinds = SpecificDayAppointmentRegistrationModal::resolveAvailableKinds([
            AppointmentSetting::VISIT_TYPE_INPERSON => 'false',
            AppointmentSetting::VISIT_TYPE_VOIP => 'true',
            AppointmentSetting::VISIT_TYPE_ONLINE => 'false',
        ]);

        $this->assertSame([AppointmentUserKindEnum::VOIP], $kinds);
    }

    public function test_it_keeps_legacy_voip_settings_compatible(): void
    {
        $kinds = SpecificDayAppointmentRegistrationModal::resolveAvailableKinds([
            AppointmentSetting::VISIT_TYPE_INPERSON => false,
            AppointmentSetting::VISIT_TYPE_ONLINE => false,
        ]);

        $this->assertSame([AppointmentUserKindEnum::VOIP], $kinds);
    }

    public function test_payment_choice_is_enabled_only_for_the_selected_paid_kind(): void
    {
        $detail = [AppointmentSetting::PAYMENT => [
            AppointmentSetting::STATUS => true,
            AppointmentSetting::IN_PERSON => [AppointmentSetting::STATUS => true],
            AppointmentSetting::VOIP => [AppointmentSetting::STATUS => false],
        ]];

        $this->assertTrue(SpecificDayAppointmentRegistrationModal::isPaymentEnabledForKind($detail, AppointmentUserKindEnum::IN_PERSION));
        $this->assertFalse(SpecificDayAppointmentRegistrationModal::isPaymentEnabledForKind($detail, AppointmentUserKindEnum::VOIP));

        $detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS] = false;
        $this->assertFalse(SpecificDayAppointmentRegistrationModal::isPaymentEnabledForKind($detail, AppointmentUserKindEnum::IN_PERSION));
    }
}
