<?php

namespace Modules\OnlineConsultation\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\OnlineConsultation\Support\ConsultationAccess;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

class CallReportDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! ConsultationAccess::enabled()) {
            $this->command?->warn('Online consultation is disabled or its tenant schema is incomplete; demo data was skipped.');

            return;
        }

        $people = [
            'patient_1' => ['09000001001', 'سارا', 'احمدی'],
            'patient_2' => ['09000001002', 'علی', 'رضایی'],
            'patient_3' => ['09000001003', 'مریم', 'کریمی'],
            'doctor_1' => ['09000002001', 'دکتر آرمان', 'محمدی'],
            'doctor_2' => ['09000002002', 'کارشناس نازنین', 'صادقی'],
        ];

        foreach ($people as $key => [$mobile, $firstName, $lastName]) {
            $user = User::updateOrCreate(['mobile' => $mobile], ['password' => Hash::make('demo-only')]);
            DB::table('user_metas')->updateOrInsert(['user_id' => $user->id, 'meta_key' => UserMetaEnum::FIRST_NAME->value], ['meta_value' => $firstName, 'updated_at' => now(), 'created_at' => now()]);
            DB::table('user_metas')->updateOrInsert(['user_id' => $user->id, 'meta_key' => UserMetaEnum::LAST_NAME->value], ['meta_value' => $lastName, 'updated_at' => now(), 'created_at' => now()]);
            $users[$key] = $user;
        }

        $now = now('Asia/Tehran');
        ConsultationPractitioner::updateOrCreate(['user_id' => $users['doctor_1']->id], [
            'display_name' => 'دکتر آرمان محمدی', 'kind' => 'doctor', 'active' => true,
            'availability' => 'ready', 'extension' => '9102', 'hourly_rate' => 1000000,
            'duration_minutes' => 60, 'weekly_schedule' => [],
        ]);
        ConsultationPractitioner::updateOrCreate(['user_id' => $users['doctor_2']->id], [
            'display_name' => 'کارشناس نازنین صادقی', 'kind' => 'expert', 'active' => true,
            'availability' => 'ready', 'extension' => '9205', 'hourly_rate' => 750000,
            'duration_minutes' => 45, 'weekly_schedule' => [],
        ]);
        $appointments = [
            ['VOIP-DEMO-1001', 'patient_1', 'doctor_1', $now->copy()->subDay()->setTime(10, 0), '10:00:00', '11:00:00'],
            ['VOIP-DEMO-1002', 'patient_2', 'doctor_2', $now->copy()->setTime(14, 0), '14:00:00', '14:45:00'],
            ['VOIP-DEMO-1003', 'patient_3', 'doctor_1', $now->copy()->addDay()->setTime(9, 30), '09:30:00', '10:15:00'],
        ];
        foreach ($appointments as [$tracking, $patient, $doctor, $visit, $start, $end]) {
            $items[$tracking] = AppointmentUser::updateOrCreate(['tracking_code' => $tracking], [
                'user_id' => $users[$patient]->id, 'doctor_id' => $users[$doctor]->id,
                'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => $visit,
                'start_time' => $start, 'end_time' => $end, 'details' => ['demo_call_report' => true],
            ]);
        }

        // A dense same-day dataset for reviewing every dashboard card and filter.
        for ($index = 1; $index <= 18; $index++) {
            $mobile = sprintf('09000003%03d', $index);
            $patient = User::updateOrCreate(['mobile' => $mobile], ['password' => Hash::make('demo-only')]);
            DB::table('user_metas')->updateOrInsert(
                ['user_id' => $patient->id, 'meta_key' => UserMetaEnum::FIRST_NAME->value],
                ['meta_value' => 'بیمار تستی '.$index, 'updated_at' => now(), 'created_at' => now()]
            );
            DB::table('user_metas')->updateOrInsert(
                ['user_id' => $patient->id, 'meta_key' => UserMetaEnum::LAST_NAME->value],
                ['meta_value' => $index % 2 ? 'احمدی' : 'رضایی', 'updated_at' => now(), 'created_at' => now()]
            );

            $visit = $index <= 14
                ? $now->copy()->startOfDay()->addMinutes(15 + ($index * 20))
                : $now->copy()->endOfDay()->subMinutes((18 - $index) * 45 + 15);
            $tracking = sprintf('VOIP-DEMO-TODAY-%02d', $index);
            $start = $visit->format('H:i:s');
            $end = $visit->copy()->addHour()->format('H:i:s');
            $items[$tracking] = AppointmentUser::updateOrCreate(['tracking_code' => $tracking], [
                'user_id' => $patient->id, 'doctor_id' => $users['doctor_1']->id,
                'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => $visit,
                'start_time' => $start, 'end_time' => $end,
                'details' => ['demo_call_report' => true, 'dashboard_scenario' => $index],
            ]);

            if ($index <= 6) {
                $this->storeCall($items[$tracking], $users['doctor_1'], sprintf('DEMO-TODAY-%02d-MISSED', $index), $visit->copy()->addMinutes(2), 'NOANSWER', 'DIRECT', 0, 12, '102', $index % 2 ? 'INBOUND' : 'OUTBOUND');
                $this->storeCall($items[$tracking], $users['doctor_1'], sprintf('DEMO-TODAY-%02d-ANSWERED', $index), $visit->copy()->addMinutes(8), 'ANSWERED', 'DIRECT', 240 + ($index * 150), 5, '102', $index % 2 ? 'OUTBOUND' : 'INBOUND');
            } elseif (in_array($index, [9, 10], true)) {
                $this->storeCall($items[$tracking], $users['doctor_1'], sprintf('DEMO-TODAY-%02d-NOANSWER', $index), $visit->copy()->addMinutes(3), 'NOANSWER', 'DIRECT', 0, 35, '102');
            } elseif (in_array($index, [11, 12], true)) {
                $this->storeCall($items[$tracking], $users['doctor_1'], sprintf('DEMO-TODAY-%02d-PATIENT-NOANSWER', $index), $visit->copy()->addMinutes(3), 'NOANSWER', 'DIRECT', 0, 28, $mobile, 'OUTBOUND');
            } elseif ($index === 13) {
                $this->storeCall($items[$tracking], $users['doctor_1'], 'DEMO-TODAY-13-SHORT', $visit->copy()->addMinutes(4), 'ANSWERED', 'DIRECT', 35, 4, '102');
            } elseif ($index === 14) {
                $this->storeCall($items[$tracking], $users['doctor_1'], 'DEMO-TODAY-14-FAILED', $visit->copy()->addMinutes(2), 'BUSY', 'NONE', 0, 8, null);
            } elseif ($index === 18) {
                $this->storeCall($items[$tracking], $users['doctor_1'], 'DEMO-TODAY-18-IN-PROGRESS', $now->copy(), 'ANSWERED', 'DIRECT', 420, 3, '102');
            }
        }

        $this->storeCall($items['VOIP-DEMO-1001'], $users['doctor_1'], 'DEMO-CALL-1001-A', $now->copy()->subDay()->setTime(9, 52), 'CALLER_ABANDONED', 'NONE', 0, 130, null);
        $this->storeCall($items['VOIP-DEMO-1001'], $users['doctor_1'], 'DEMO-CALL-1001-B', $now->copy()->subDay()->setTime(10, 3), 'ANSWERED', 'DIRECT', 1080, 9, '102');
        $this->storeCall($items['VOIP-DEMO-1001'], $users['doctor_1'], 'DEMO-CALL-1001-C', $now->copy()->subDay()->setTime(10, 42), 'ANSWERED', 'DIVERTED', 620, 18, '09121111111');
        $this->storeCall($items['VOIP-DEMO-1002'], $users['doctor_2'], 'DEMO-CALL-1002-A', $now->copy()->setTime(13, 48), 'NOT_DIALED', 'NONE', 0, 0, null);
        $this->storeCall($items['VOIP-DEMO-1002'], $users['doctor_2'], 'DEMO-CALL-1002-B', $now->copy()->setTime(14, 2), 'NOANSWER', 'NONE', 0, 4, null);
        $this->storeCall($items['VOIP-DEMO-1002'], $users['doctor_2'], 'DEMO-CALL-1002-C', $now->copy()->setTime(14, 12), 'ANSWERED', 'DIRECT', 840, 6, '205');
        $this->storeCall($items['VOIP-DEMO-1003'], $users['doctor_1'], 'DEMO-CALL-1003-A', $now->copy()->setTime(18, 25), 'NOT_DIALED', 'NONE', 0, 0, null);

        $billingService = app(AppointmentBillingService::class);
        foreach ($items as $appointment) $billingService->ensure($appointment);

        // Keep three visible financial states for UI review: pending, approved, and completed + corrected.
        $approvedDemo = $items['VOIP-DEMO-1002']->billingRecord;
        if ($approvedDemo?->refund_status === 'pending') {
            $billingService->approveMinutes($approvedDemo, 25, $users['doctor_2'], 'اصلاح نمایشی: ۶ دقیقه بابت پیگیری تکمیلی کسر شد.');
        }
        $completedDemo = $items['VOIP-DEMO-1003']->billingRecord;
        if ($completedDemo?->refund_status === 'pending') {
            $billingService->approveMinutes($completedDemo, 15, $users['doctor_1'], 'تأیید نمایشی بازگشت بخشی از زمان رزروشده.');
            $billingService->refund($completedDemo->refresh(), $users['doctor_1']);
        }
        if ($completedDemo?->fresh()->refund_status === 'completed') {
            $billingService->correctCompletedRefund(
                $completedDemo->fresh(), 20, $users['doctor_1'],
                'اصلاح نمایشی پس از بازگشت اولیه.', '00000000-0000-4000-8000-000000001003'
            );
        }

        $todayApproved = $items['VOIP-DEMO-TODAY-01']->billingRecord;
        if ($todayApproved?->refund_status === 'pending') {
            $billingService->approveMinutes($todayApproved, 30, $users['doctor_1'], 'تأیید نمایشی بخشی از زمان استفاده‌نشده.');
        }
        $todayRefunded = $items['VOIP-DEMO-TODAY-02']->billingRecord;
        if ($todayRefunded?->refund_status === 'pending') {
            $billingService->approveMinutes($todayRefunded, 20, $users['doctor_1'], 'بازگشت وجه نمایشی داشبورد روزانه.');
            $billingService->refund($todayRefunded->refresh(), $users['doctor_1']);
        }
        $todayRefunded = $todayRefunded?->fresh();
        $todayEffectiveRefund = (int) ($todayRefunded?->refunded_amount ?? 0) + (int) ($todayRefunded?->adjustments()->sum('amount_change') ?? 0);
        $todayRoundedRefund = $billingService->amountForMinutes((int) ($todayRefunded?->hourly_rate_snapshot ?? 0), 20);
        if ($todayRefunded?->refund_status === 'completed' && $todayEffectiveRefund !== $todayRoundedRefund) {
            $billingService->correctCompletedRefund(
                $todayRefunded, 20, $users['doctor_1'],
                'رند کردن مبلغ نمایشی به نزدیک‌ترین هزار تومان.', '00000000-0000-4000-8000-000000002002'
            );
        }
        // Normalize legacy completed demo refunds without rewriting their original ledger rows.
        foreach ($items as $demoAppointment) {
            $demoBilling = $demoAppointment->billingRecord?->fresh();
            if (! $demoBilling || $demoBilling->refund_status !== 'completed') {
                continue;
            }
            $effectiveMinutes = (int) ($demoBilling->adjustments()->latest('id')->value('corrected_unused_minutes') ?? $demoBilling->approved_unused_minutes);
            $effectiveAmount = (int) $demoBilling->refunded_amount + (int) $demoBilling->adjustments()->sum('amount_change');
            $roundedAmount = $billingService->amountForMinutes((int) $demoBilling->hourly_rate_snapshot, $effectiveMinutes);
            if ($effectiveAmount !== $roundedAmount) {
                $billingService->correctCompletedRefund(
                    $demoBilling, $effectiveMinutes, User::findOrFail($demoBilling->practitioner_id),
                    'رند کردن مبلغ نمایشی به نزدیک‌ترین هزار تومان.',
                    sprintf('10000000-0000-4000-8000-%012d', $demoBilling->id)
                );
            }
        }

        AppointmentCallLog::updateOrCreate(['call_id' => 'DEMO-CALL-NO-APPOINTMENT'], [
            'appointment_id' => null, 'patient_phone' => '09000001999', 'appointment_state' => 'NO_APPOINTMENT',
            'final_result' => 'NOT_DIALED', 'connection_type' => 'NONE', 'direction' => 'INBOUND',
            'call_entered_at' => $now->copy()->subHours(2), 'ended_at' => $now->copy()->subHours(2)->addSeconds(15),
            'total_duration_seconds' => 15, 'disconnected_by' => 'SYSTEM',
            'raw_payload' => ['demo' => true, 'scenario' => 'no_appointment'],
        ]);

        $smsDeliveries = [
            ['DEMO-SMS-1001', 'VOIP-DEMO-1001', 'patient_reminder', '09000001001', 'consultation_patient_reminder', $now->copy()->subDay()->setTime(8, 0), 'sent', 1, $now->copy()->subDay()->setTime(8, 1), 'ارسال موفق آزمایشی', null],
            ['DEMO-SMS-1002', 'VOIP-DEMO-1002', 'practitioner_reminder', '09000002002', 'consultation_practitioner_reminder', $now->copy()->subMinutes(20), 'pending', 0, null, null, null],
            ['DEMO-SMS-1003', 'VOIP-DEMO-1002', 'patient_reminder', '09000001002', 'consultation_patient_reminder', $now->copy()->subHour(), 'retrying', 2, null, 'پاسخ موقت درگاه آزمایشی', 'عدم پاسخ موقت سرویس پیامک'],
            ['DEMO-SMS-1004', 'VOIP-DEMO-1003', 'practitioner_reminder', '09000002001', 'consultation_practitioner_reminder', $now->copy()->addHours(2), 'failed', 3, null, 'خطای نمایشی درگاه', 'شماره مقصد در محیط آزمایشی در دسترس نیست'],
            ['DEMO-SMS-1005', 'VOIP-DEMO-1003', 'patient_reminder', '09000001003', 'consultation_patient_reminder', $now->copy()->addHours(3), 'skipped', 0, null, null, 'ارسال این یادآوری به‌صورت نمایشی رد شد'],
        ];
        foreach ($smsDeliveries as [$key, $tracking, $type, $recipient, $template, $scheduledAt, $status, $attempts, $sentAt, $providerResponse, $error]) {
            ConsultationSmsDelivery::updateOrCreate(['deduplication_key' => $key], [
                'appointment_id' => $items[$tracking]->id,
                'practitioner_id' => $items[$tracking]->doctor_id,
                'type' => $type, 'recipient' => $recipient, 'template' => $template,
                'scheduled_at' => $scheduledAt, 'sent_at' => $sentAt, 'status' => $status,
                'attempts' => $attempts, 'provider_response' => $providerResponse, 'error_message' => $error,
                'payload' => ['demo' => true, 'tracking_code' => $tracking],
            ]);
        }
    }

    private function storeCall(AppointmentUser $appointment, User $operator, string $callId, $enteredAt, string $result, string $connection, int $talk, int $ring, ?string $destination, string $direction = 'INBOUND'): void
    {
        $answeredAt = $result === 'ANSWERED' ? $enteredAt->copy()->addSeconds($ring + 3) : null;
        $total = 3 + $ring + $talk;
        AppointmentCallLog::updateOrCreate(['call_id' => $callId], [
            'appointment_id' => $appointment->id, 'patient_phone' => $appointment->user->mobile,
            'operator_id' => $operator->id, 'appointment_start_at' => $appointment->date_visit,
            'appointment_end_at' => $appointment->date_visit->copy()->setTimeFromTimeString($appointment->end_time),
            'call_entered_at' => $enteredAt, 'dial_started_at' => $connection === 'NONE' ? null : $enteredAt->copy()->addSeconds(3),
            'answered_at' => $answeredAt, 'ended_at' => $enteredAt->copy()->addSeconds($total),
            'appointment_state' => $connection === 'NONE' && $result === 'NOT_DIALED' ? 'BEFORE_APPOINTMENT' : 'IN_APPOINTMENT_TIME',
            'final_result' => $result, 'connection_type' => $connection, 'primary_extension' => $operator->mobile === '09000002001' ? '102' : '205',
            'destination' => $destination, 'connected_destination' => $destination, 'responded_by' => $answeredAt ? $destination : null,
            'direction' => $direction, 'wait_duration_seconds' => 3, 'ring_duration_seconds' => $ring,
            'talk_duration_seconds' => $talk, 'total_duration_seconds' => $total,
            'disconnected_by' => $answeredAt ? 'PATIENT' : 'SYSTEM', 'hangup_cause' => $answeredAt ? 16 : 19,
            'attempts' => $connection === 'NONE' ? [] : [[
                'order' => 1, 'type' => $connection === 'DIVERTED' ? 'DIVERT' : 'PRIMARY',
                'destination' => $destination, 'dial_started_at' => $enteredAt->copy()->addSeconds(3)->toIso8601String(),
                'answered_at' => $answeredAt?->toIso8601String(), 'ended_at' => $enteredAt->copy()->addSeconds($total)->toIso8601String(),
                'dial_status' => $answeredAt ? 'ANSWER' : $result, 'ring_duration_seconds' => $ring, 'talk_duration_seconds' => $talk,
            ]],
            'additional_data' => ['demo' => true, 'note' => 'داده نمایشی گزارش تماس'],
            'raw_payload' => ['demo' => true, 'call_id' => $callId, 'source' => 'CallReportDemoSeeder'],
        ]);
    }
}
