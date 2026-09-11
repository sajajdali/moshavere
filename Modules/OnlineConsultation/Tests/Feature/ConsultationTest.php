<?php

namespace Modules\OnlineConsultation\Tests\Feature;

use App\Models\Tenant;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Modules\OnlineConsultation\Jobs\SendConsultationSms;
use Modules\OnlineConsultation\Models\ConsultationSmsDelivery;
use Modules\OnlineConsultation\Models\ConsultationSmsReminderRule;
use Modules\OnlineConsultation\Services\ConsultationReminderScheduler;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentConsultantHangup;
use Modules\OnlineConsultation\Models\AppointmentCallbackRequest;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\OnlineConsultation\Services\ConsultantDashboardService;
use Modules\OnlineConsultation\Services\ConsultantFinancialReportService;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Front\app\Models\FeedBack;
use Modules\User\app\Models\UserWallet;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated SQLite only: never run tests against the installed tenant databases.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'database/migrations/2023_08_02_073105_create_users_table.php',
            'database/migrations/2023_08_02_073110_create_user_metas_table.php',
            'database/migrations/2024_01_30_115541_create_permission_tables.php',
            'Modules/Setting/Database/Migrations/tenant/2022_11_30_062449_create_settings_table.php',
            'Modules/AppointmentUser/database/migrations/tenant/2024_03_19_094623_create_appointment_users_table.php',
            'Modules/Front/Database/Migrations/tenant/2024_05_13_155319_create_feedBacks_table.php',
            'database/migrations/tenant/2024_03_29_160521_create_short_links_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_06_000001_create_online_consultation_tables.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_06_000003_create_voip_request_logs_table.php',
            'Modules/User/Database/Migrations/tenant/2026_02_23_181827_create_user_wallets_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000004_create_appointment_call_logs_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000005_create_appointment_billing_records.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000006_create_appointment_billing_adjustments.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000007_create_consultation_sms_deliveries.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000008_create_consultation_sms_reminder_rules.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000009_create_appointment_consultant_hangups_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000010_create_appointment_consultant_no_answers_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000011_create_appointment_consultation_cases.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000012_add_note_to_appointment_consultation_cases.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_10_000013_add_financial_split_to_consultation_billing.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_11_000014_create_appointment_callback_requests_table.php',
        ] as $path) {
            (require base_path($path))->up();
        }
        foreach (['appointment_settings', 'services', 'places', 'transactions'] as $table) {
            Schema::create($table, fn ($blueprint) => $blueprint->id());
        }
        Schema::create('events', function ($table) {
            $table->id();
            $table->dateTime('date')->nullable();
            $table->boolean('is_holiday')->default(false);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('SUPER_ADMIN', 'web');
        Permission::findOrCreate('ADMIN_ACCESS', 'web');
        $this->manager = User::create(['mobile' => '09120000001', 'password' => 'test-password']);
        $this->manager->givePermissionTo(['ADMIN_ACCESS', 'ONLINE_CONSULTATION_MANAGE']);
        tenancy()->tenant = new Tenant(['id' => 'test-only', 'online_consultation_enabled' => true]);
        tenancy()->initialized = true;
        $this->withoutMiddleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class]);
        $this->actingAs($this->manager);
        \Illuminate\Support\Facades\Artisan::call('auth:permission-sync');
    }

    protected function tearDown(): void
    {
        tenancy()->initialized = false;
        tenancy()->tenant = null;
        parent::tearDown();
    }

    private function profile(array $overrides = []): array
    {
        return array_replace([
            'user_id' => $this->manager->id, 'display_name' => 'کارشناس آزمون', 'kind' => 'expert',
            'active' => '1', 'app_access' => '1', 'availability' => 'ready', 'extension' => '201',
            'sip_secret' => 'private-test-secret',
            'hourly_rate' => '1000000',
            'payout_hourly_rate' => '600000',
        ], $overrides);
    }

    private function noShowAppointment(array $overrides = []): AppointmentUser
    {
        ConsultationPractitioner::create($this->profile(['payout_hourly_rate' => 800000]));
        $patient = User::create(['mobile' => '09125550123', 'password' => 'test-password']);
        return AppointmentUser::withoutEvents(fn () => AppointmentUser::create(array_replace([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id, 'status' => 1, 'type' => 1, 'kind' => 3,
            'date_visit' => now('Asia/Tehran')->subDay()->setTime(10, 0), 'start_time' => '10:00:00', 'end_time' => '11:00:00',
            'tracking_code' => 'PATIENT-ABSENCE-TEST',
            'details' => [AppointmentUser::DETAIL_PAYMENT => [AppointmentUser::DETAIL_PAYMENT_PRICE => ['int' => 1000000]]],
        ], $overrides)));
    }

    private function noShowUrl(AppointmentUser $appointment): string
    {
        return '/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/patient-no-show';
    }

    public function test_patient_no_show_settles_full_fee_once_and_appears_in_reports(): void
    {
        $appointment = $this->noShowAppointment();
        for ($i = 0; $i < 2; $i++) {
            $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertRedirect()->assertSessionHasNoErrors();
        }
        $billing = $appointment->billingRecord()->firstOrFail();
        $billing = app(AppointmentBillingService::class)->refresh($billing);
        $this->assertSame(1000000, $billing->used_amount);
        $this->assertSame(800000, $billing->practitioner_earned_amount);
        $this->assertSame(200000, $billing->platform_profit_amount);
        $this->assertSame(0, $billing->refunded_amount);
        $this->assertSame(0, $billing->suggested_refund_amount);
        $this->assertSame(0, $billing->answered_talk_seconds);
        $this->assertSame('completed', $billing->refund_status);
        $this->assertNull($billing->wallet_transaction_id);
        $this->assertDatabaseCount('user_wallets', 0);
        $this->assertSame(1, $billing->audits()->where('action', 'patient_no_show_settled')->count());
        $case = $appointment->consultationCase()->firstOrFail();
        $this->assertSame('PATIENT_NO_SHOW', $case->state);
        $this->assertSame(1, $case->events()->where('action', 'PATIENT_NO_SHOW')->count());
        $this->assertSame(800000, $case->events()->first()->snapshot['billing']['practitioner_earned_amount']);
        $appointment->load(['callLogs', 'billingRecord.adjustments', 'consultationCase', 'feedbacks']);
        $stats = app(ConsultantDashboardService::class)->stats(collect([$appointment]));
        $this->assertSame(1, $stats['patient_no_show']);
        $this->assertSame(0, $stats['completed']);
        $this->assertSame(800000, $stats['practitioner_income']);
        $this->assertSame(1, app(ConsultantFinancialReportService::class)->metrics(collect([$appointment]))['patient_no_show']);
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)->assertOk()->assertSee('عدم حضور بیمار')->assertSee('800,000')->assertDontSee('ثبت عملیات اصلاحی جدید');
        $this->get('/admin/online-consultation/call-reports')->assertOk()->assertSee('PATIENT-ABSENCE-TEST');
        $this->get('/admin/online-consultation/financial-report')->assertOk()->assertSee('عدم حضور بیمار');
    }

    public function test_patient_no_show_requires_confirmation_permission_payment_and_phone_appointment(): void
    {
        $appointment = $this->noShowAppointment();
        $this->post($this->noShowUrl($appointment))->assertSessionHasErrors('no_show_confirmed');
        $appointment->user->givePermissionTo(['ADMIN_ACCESS', 'ONLINE_CONSULTATION_MANAGE']);
        $this->actingAs($appointment->user)->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertForbidden();
        $this->actingAs($this->manager);
        foreach ([['status' => 0], ['status' => AppointmentUserStatusEnum::STATUS_CANCEL->value], ['status' => 1, 'kind' => 1]] as $values) {
            $appointment->update($values);
            $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        }
        $this->assertDatabaseCount('user_wallets', 0);
        $this->assertDatabaseMissing('appointment_consultation_cases', ['state' => 'PATIENT_NO_SHOW']);
    }

    public function test_patient_no_show_requires_end_of_window_including_midnight(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 00:15:00', 'Asia/Tehran'));
        $appointment = $this->noShowAppointment(['date_visit' => '2026-09-10 23:30:00', 'start_time' => '23:30:00', 'end_time' => '00:30:00']);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 00:30:00', 'Asia/Tehran'));
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 00:30:01', 'Asia/Tehran'));
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
        $this->travelBack();
    }

    public function test_patient_no_show_ignores_early_calls(): void
    {
        $appointment = $this->noShowAppointment();
        AppointmentCallLog::create([
            'raw_payload' => [], 'call_id' => 'EARLY-ANSWERED', 'appointment_id' => $appointment->id,
            'patient_phone' => $appointment->user->mobile, 'direction' => 'INBOUND',
            // The timestamp must win over an incorrect state received from the PBX.
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'ANSWERED',
            'appointment_start_at' => $appointment->date_visit, 'appointment_end_at' => $appointment->date_visit->copy()->addHour(),
            'call_entered_at' => $appointment->date_visit->copy()->subSecond(), 'answered_at' => $appointment->date_visit->copy()->subSecond(),
            'connection_type' => 'DIRECT', 'talk_duration_seconds' => 1,
        ]);
        // A secondary incident for the same early call must not reintroduce it as
        // in-window evidence, even if the incident itself arrives after start.
        \Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer::create([
            'raw_payload' => [], 'call_id' => 'EARLY-ANSWERED', 'appointment_id' => $appointment->id,
            'ring_started_at' => $appointment->date_visit->copy()->subSecond(),
            'no_answer_at' => $appointment->date_visit->copy()->addSecond(),
        ]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
    }

    public function test_patient_no_show_rejects_calls_in_the_reserved_window(): void
    {
        $appointment = $this->noShowAppointment();
        foreach (['NOANSWER', 'BUSY', 'FAILED', 'CALLER_ABANDONED', 'NOT_DIALED', 'ANSWERED'] as $result) {
            $call = AppointmentCallLog::create([
                'raw_payload' => [], 'call_id' => 'ABSENCE-'.$result, 'appointment_id' => $appointment->id,
                'patient_phone' => $appointment->user->mobile, 'direction' => 'INBOUND',
                'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => $result,
                'call_entered_at' => $appointment->date_visit->copy()->addMinute(),
                'connection_type' => 'NONE', 'talk_duration_seconds' => $result === 'ANSWERED' ? 1 : 0,
            ]);
            $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
            $this->assertStringContainsString('بیمار در بازه نوبت 1 بار تماس گرفته', session('errors')->first('no_show'));
            $call->delete();
        }
        AppointmentCallLog::create(['raw_payload' => [], 'call_id' => 'UNKNOWN-DIRECTION', 'appointment_id' => $appointment->id, 'patient_phone' => $appointment->user->mobile, 'direction' => null, 'appointment_state' => 'IN_APPOINTMENT_TIME', 'call_entered_at' => $appointment->date_visit->copy()->addMinute(), 'final_result' => 'FAILED', 'connection_type' => 'NONE']);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->assertDatabaseCount('user_wallets', 0);
    }

    public function test_patient_no_show_rejects_incidents_without_final_call_log(): void
    {
        $appointment = $this->noShowAppointment();
        \Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer::create([
            'raw_payload' => [], 'call_id' => 'NOT-YET-LOGGED', 'appointment_id' => $appointment->id,
            'ring_started_at' => $appointment->date_visit->copy()->addMinute(),
            'no_answer_at' => $appointment->date_visit->copy()->addMinutes(2),
        ]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->assertStringContainsString('1 تماس', session('errors')->first('no_show'));
    }

    public function test_patient_no_show_ignores_early_incidents_without_final_call_log(): void
    {
        $appointment = $this->noShowAppointment();
        \Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer::create([
            'raw_payload' => [], 'call_id' => 'EARLY-NOT-YET-LOGGED', 'appointment_id' => $appointment->id,
            'ring_started_at' => $appointment->date_visit->copy()->subMinutes(2),
            'no_answer_at' => $appointment->date_visit->copy()->subMinute(),
        ]);

        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
    }

    public function test_patient_no_show_cannot_overwrite_existing_settlement_or_be_refunded_or_reopened(): void
    {
        $appointment = $this->noShowAppointment();
        $service = app(AppointmentBillingService::class);
        $billing = $service->ensure($appointment);
        $billing->update(['refund_status' => 'completed']);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $billing->update(['refund_status' => 'pending']);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/reopen', ['reopen_confirmed' => 1, 'reopen_reason' => 'آزمون بازگشایی پرونده'])->assertSessionHasErrors('reopen_reason');
        foreach (['cancel', 'delete'] as $mutation) {
            try {
                $mutation === 'cancel' ? $appointment->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]) : $appointment->fresh()->delete();
                $this->fail('Finalized no-show appointments must retain their audit history.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('appointment', $exception->errors());
            }
        }
        foreach (['refund', 'approve', 'confirm', 'correct'] as $action) {
            try {
                match ($action) {
                    'refund' => $service->refund($billing, $this->manager),
                    'approve' => $service->approveMinutes($billing, 60, $this->manager, 'تست'),
                    'confirm' => $service->confirmAndRefund($billing, 60, $this->manager, 'تست'),
                    'correct' => $service->correctCompletedRefund($billing, 60, $this->manager, 'تست', (string) Str::uuid()),
                };
                $this->fail('No-show billing must remain locked.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('refund', $exception->errors());
            }
        }
        $this->assertDatabaseCount('user_wallets', 0);
    }

    public function test_patient_no_show_preserves_late_call_evidence_and_financial_snapshot(): void
    {
        $appointment = $this->noShowAppointment();
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'call_id' => 'DELAYED-REPORT', 'appointment_id' => $appointment->id, 'patient_phone' => $appointment->user->mobile,
            'direction' => 'INBOUND', 'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'NOANSWER', 'connection_type' => 'NONE',
            'call_entered_at' => $appointment->date_visit->toIso8601String(),
        ]);
        for ($i = 0; $i < 2; $i++) {
            $response = app(\Modules\Api\Http\Controllers\Voip\CallLogController::class)->store($request);
            $this->assertSame(200, $response->getStatusCode());
        }
        $this->assertDatabaseHas('appointment_call_logs', ['call_id' => 'DELAYED-REPORT']);
        $this->assertSame(1, $appointment->consultationCase->events()->where('action', 'NO_SHOW_CALL_RECEIVED')->count());
        $this->assertSame(800000, $appointment->billingRecord->practitioner_earned_amount);
        $this->assertDatabaseCount('user_wallets', 0);
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)->assertOk()->assertSee('DELAYED-REPORT')->assertSee('نیازمند بررسی');
    }

    public function test_patient_no_show_rejects_unlinked_patient_calls_in_the_reserved_window(): void
    {
        $appointment = $this->noShowAppointment();
        AppointmentCallLog::create([
            'call_id' => 'UNLINKED-PATIENT-CALL', 'patient_phone' => '+98 912 555 0123',
            'call_entered_at' => $appointment->date_visit->copy()->addMinute(),
            'direction' => 'INBOUND', 'final_result' => 'NOANSWER', 'raw_payload' => [],
        ]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->assertDatabaseCount('appointment_billing_records', 0);
    }

    public function test_patient_no_show_allows_only_unanswered_outbound_attempts_and_uses_reserved_duration(): void
    {
        $appointment = $this->noShowAppointment(['end_time' => '10:30:00']);
        $call = AppointmentCallLog::create([
            'call_id' => 'OUTBOUND-CALL', 'appointment_id' => $appointment->id, 'patient_phone' => $appointment->user->mobile,
            'direction' => 'OUTBOUND', 'final_result' => 'ANSWERED', 'raw_payload' => [], 'talk_duration_seconds' => 1,
            'call_entered_at' => $appointment->date_visit->copy()->addMinute(),
        ]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $call->update(['final_result' => 'NOANSWER', 'talk_duration_seconds' => 0]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasNoErrors();
        $this->assertSame(400000, $appointment->billingRecord->practitioner_earned_amount);
        $this->assertSame(600000, $appointment->billingRecord->platform_profit_amount);
        $this->assertDatabaseCount('user_wallets', 0);
    }

    public function test_patient_no_show_rejects_missing_schedule_and_payout_rate(): void
    {
        $appointment = $this->noShowAppointment(['end_time' => null]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $appointment->update(['end_time' => '11:00:00']);
        ConsultationPractitioner::where('user_id', $appointment->doctor_id)->update(['payout_hourly_rate' => 0]);
        $this->post($this->noShowUrl($appointment), ['no_show_confirmed' => 1])->assertSessionHasErrors('no_show');
        $this->assertDatabaseCount('appointment_billing_records', 0);
    }

    public function test_missing_consultation_schema_does_not_break_appointment_or_short_link_creation(): void
    {
        Schema::drop('consultation_sms_deliveries');
        $patient = User::create(['mobile' => '09120000009', 'password' => 'test-password']);

        $appointment = AppointmentUser::create([
            'user_id' => $patient->id,
            'doctor_id' => $this->manager->id,
            'status' => 1,
            'type' => 1,
            'kind' => 1,
            'date_visit' => now()->addHour(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'tracking_code' => 'NO-CONSULTATION-SCHEMA',
        ]);

        $this->assertDatabaseHas('appointment_users', ['id' => $appointment->id]);
        $this->assertStringContainsString('/s/', $appointment->shortLinkUrl(false));
        $this->assertDatabaseHas('short_links', [
            'shortlinkable_id' => $appointment->id,
            'shortlinkable_type' => AppointmentUser::class,
        ]);
        $this->assertDatabaseCount('appointment_billing_records', 0);
    }

    public function test_practitioner_creation_encrypts_secrets_and_validates_duplicate_extensions(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors()->assertRedirect();
        $person = ConsultationPractitioner::firstOrFail();
        $this->assertSame('private-test-secret', $person->sip_secret);
        $this->assertNotSame('private-test-secret', $person->getRawOriginal('sip_secret'));
        $this->assertArrayNotHasKey('sip_secret', $person->toArray());
        $other = User::create(['password' => 'test-password']);
        $this->post('/admin/online-consultation/practitioners', $this->profile(['user_id' => $other->id]))
            ->assertSessionHasErrors('extension');
        $this->assertSame(1, ConsultationPractitioner::count());
        $this->assertNull(session()->getOldInput('sip_secret'));
    }

    public function test_account_reassignment_is_rejected(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        $person = ConsultationPractitioner::firstOrFail();
        $other = User::create(['password' => 'test-password']);
        $this->put('/admin/online-consultation/practitioners/'.$person->id, $this->profile(['user_id' => $other->id]))
            ->assertSessionHasErrors('user_id');
        $this->assertSame($this->manager->id, $person->fresh()->user_id);
    }

    public function test_blanking_password_preserves_it_and_deactivation_revokes_app_access(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        $person = ConsultationPractitioner::firstOrFail();
        $this->put('/admin/online-consultation/practitioners/'.$person->id, $this->profile(['sip_secret' => '', 'active' => '0']))->assertSessionHasNoErrors();
        $this->assertSame('private-test-secret', $person->fresh()->sip_secret);
        $this->assertFalse($person->fresh()->app_access);
        $this->assertSame('offline', $person->fresh()->availability);
    }

    public function test_disabled_site_blocks_admin_writes_and_app_api(): void
    {
        tenancy()->tenant->online_consultation_enabled = false;
        $this->postJson('/admin/online-consultation/practitioners', $this->profile())->assertNotFound();
        $this->getJson('/api/online-consultation/me')->assertNotFound();
        $this->assertSame(0, ConsultationPractitioner::count());
    }

    public function test_admin_permission_does_not_implicitly_grant_consultation_management(): void
    {
        $this->manager->revokePermissionTo('ONLINE_CONSULTATION_MANAGE');
        $this->postJson('/admin/online-consultation/practitioners', $this->profile())->assertForbidden();
    }

    public function test_app_api_requires_both_site_and_individual_access_and_hides_sip_credentials(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        $this->getJson('/api/online-consultation/me')->assertForbidden();
        ConsultationSetting::current()->update(['app_enabled' => true]);
        $this->getJson('/api/online-consultation/me')->assertOk()->assertJsonPath('data.display_name', 'کارشناس آزمون')
            ->assertJsonMissingPath('data.sip_secret')->assertJsonMissingPath('data.sip_username');
        $this->putJson('/api/online-consultation/availability', ['availability' => 'busy'])->assertOk();
        ConsultationPractitioner::first()->update(['app_access' => false]);
        $this->putJson('/api/online-consultation/availability', ['availability' => 'ready'])->assertNotFound();
    }

    public function test_settings_save_enforces_recording_consent_and_preserves_secret(): void
    {
        $data = ConsultationSetting::current()->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        unset(
            $data['duration_minutes'], $data['buffer_minutes'], $data['advance_hours'],
            $data['booking_horizon_days'], $data['cancellation_hours'],
            $data['capacity_per_slot'], $data['default_fee']
        );
        $data = array_replace($data, [
            'booking_enabled' => '0', 'app_enabled' => '1', 'allow_transfer' => '0',
            'recording_requested' => '0', 'consent_required' => '1', 'voip_secret' => 'secret-value',
        ]);
        $this->put('/admin/online-consultation/settings', $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('secret-value', ConsultationSetting::current()->voip_secret);
        $data['voip_secret'] = '';
        $this->put('/admin/online-consultation/settings', $data)->assertSessionHasNoErrors();
        $this->assertSame('secret-value', ConsultationSetting::current()->voip_secret);
        $data['recording_requested'] = '1';
        $data['consent_required'] = '0';
        $this->put('/admin/online-consultation/settings', $data)->assertSessionHasErrors('consent_required');
    }

    public function test_consultant_callback_sends_exact_payload_and_keeps_an_audit_log(): void
    {
        Http::fake(['https://calls.example.test/api/v1/VoIP/request_call' => Http::response(['message' => 'queued'], 202)]);
        ConsultationSetting::current()->update([
            'voip_host' => 'https://calls.example.test',
            'voip_username' => null,
            'voip_secret' => null,
        ]);
        $appointment = $this->noShowAppointment();

        $url = '/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/callback';
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertSee('data-bs-target="#callback-request-modal"', false)
            ->assertSee('ثبت و ارسال درخواست تماس')
            ->assertSee('تأیید و تماس با بیمار')
            ->assertSee('09125550123')
            ->assertSee('201');
        $this->post($url)->assertSessionHasNoErrors()->assertSessionHas('success');

        Http::assertSent(fn ($request) => $request->url() === 'https://calls.example.test/api/v1/VoIP/request_call' && $request->data() === [
            'appointment_id' => $appointment->id,
            'patient_phone' => '09125550123',
            'advisor_extension' => '201',
            'request_id' => 'appointment-'.$appointment->id.'-callback-1',
            'requested_by' => 'consultant',
        ]);
        $this->assertDatabaseHas('appointment_callback_requests', [
            'appointment_id' => $appointment->id,
            'request_id' => 'appointment-'.$appointment->id.'-callback-1',
            'requested_by_id' => $this->manager->id,
            'status' => AppointmentCallbackRequest::STATUS_ACCEPTED,
            'http_status' => 202,
        ]);
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()->assertSee('appointment-'.$appointment->id.'-callback-1')->assertSee('پذیرفته شد');
    }

    public function test_patient_callback_appears_after_five_minutes_only_when_patient_has_not_called(): void
    {
        Http::fake(['https://calls.example.test/api/v1/VoIP/request_call' => Http::response(['message' => 'queued'], 202)]);
        ConsultationSetting::current()->update(['voip_host' => 'https://calls.example.test']);
        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 10:04:59', 'Asia/Tehran'));
        $appointment = $this->noShowAppointment([
            'date_visit' => '2026-09-11 10:00:00',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);
        $pageUrl = '/admin/online-consultation/call-reports/appointments/'.$appointment->id;
        $callbackUrl = $pageUrl.'/callback';

        $this->get($pageUrl)
            ->assertOk()
            ->assertSee('اگر بیمار تا ۵ دقیقه پس از شروع نوبت تماس نگیرد')
            ->assertDontSee($callbackUrl, false);
        $this->post($callbackUrl)->assertSessionHasErrors('callback');
        Http::assertNothingSent();

        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 10:05:00', 'Asia/Tehran'));
        $this->get($pageUrl)->assertOk()->assertSee('تماس با بیمار')->assertSee($callbackUrl, false);

        AppointmentCallLog::create([
            'raw_payload' => [], 'call_id' => 'PATIENT-CALLED-IN-GRACE', 'appointment_id' => $appointment->id,
            'patient_phone' => $appointment->user->mobile, 'direction' => 'INBOUND',
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'NOANSWER',
            'call_entered_at' => '2026-09-11 10:03:00', 'connection_type' => 'NONE',
        ]);
        $this->get($pageUrl)
            ->assertOk()
            ->assertSee('بیمار پس از شروع نوبت تماس گرفته است')
            ->assertDontSee($callbackUrl, false);
        $this->post($callbackUrl)->assertSessionHasErrors('callback');
        Http::assertNothingSent();
        $this->travelBack();
    }

    public function test_failed_consultant_callback_is_logged_with_the_server_error(): void
    {
        Http::fake(['https://calls.example.test/api/v1/VoIP/request_call' => Http::response(['message' => 'PBX unavailable'], 503)]);
        ConsultationSetting::current()->update(['voip_host' => 'https://calls.example.test']);
        $appointment = $this->noShowAppointment();

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/callback')
            ->assertSessionHasErrors('callback');

        $this->assertDatabaseHas('appointment_callback_requests', [
            'appointment_id' => $appointment->id,
            'status' => AppointmentCallbackRequest::STATUS_FAILED,
            'http_status' => 503,
        ]);
    }

    public function test_consultant_callback_accepts_only_http_202_and_does_not_follow_redirects(): void
    {
        Http::fake([
            'http://rokhvanak.ir:2214/api/v1/VoIP/request_call' => Http::sequence()
                ->push('', 302, ['Location' => '/login'])
                ->push(['message' => 'login page'], 200),
        ]);
        ConsultationSetting::current()->update([
            'voip_host' => 'http://rokhvanak.ir:2214/',
            'voip_username' => 'api-user',
            'voip_secret' => 'api-password',
        ]);
        $appointment = $this->noShowAppointment();

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/callback')
            ->assertSessionHasErrors('callback');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://rokhvanak.ir:2214/api/v1/VoIP/request_call'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('api-user:api-password'));
        });
        $this->assertDatabaseHas('appointment_callback_requests', [
            'appointment_id' => $appointment->id,
            'endpoint' => 'http://rokhvanak.ir:2214/api/v1/VoIP/request_call',
            'status' => AppointmentCallbackRequest::STATUS_FAILED,
            'http_status' => 302,
        ]);

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/callback')
            ->assertSessionHasErrors('callback');
        $this->assertDatabaseHas('appointment_callback_requests', [
            'appointment_id' => $appointment->id,
            'request_id' => 'appointment-'.$appointment->id.'-callback-2',
            'status' => AppointmentCallbackRequest::STATUS_FAILED,
            'http_status' => 200,
        ]);
        $this->assertStringContainsString('فقط پاسخ HTTP 202', session('errors')->first('callback'));
    }

    public function test_missing_optional_callback_table_does_not_turn_the_whole_module_into_404(): void
    {
        $appointment = $this->noShowAppointment();
        Schema::drop('appointment_callback_requests');

        $this->assertTrue(\Modules\OnlineConsultation\Support\ConsultationAccess::enabled());
        $this->assertFalse(\Modules\OnlineConsultation\Support\ConsultationAccess::schemaReady());
        $callbackUrl = '/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/callback';
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertDontSee($callbackUrl, false);
        $this->post($callbackUrl)
            ->assertSessionHasErrors('callback');
    }

    public function test_manager_can_open_complete_user_activity_report_with_real_totals(): void
    {
        Permission::findOrCreate('user', 'web');
        $this->manager->givePermissionTo('user');
        $patient = User::create(['mobile' => '09121112233', 'password' => 'test-password']);
        $appointment = AppointmentUser::create([
            'user_id' => $patient->id,
            'doctor_id' => $this->manager->id,
            'status' => 1,
            'type' => 1,
            'kind' => 3,
            'date_visit' => now(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'tracking_code' => 'USER-REPORT-1',
        ]);
        AppointmentCallLog::create([
            'appointment_id' => $appointment->id,
            'call_id' => 'USER-REPORT-CALL-1',
            'patient_phone' => $patient->mobile,
            'direction' => 'INBOUND',
            'appointment_state' => 'IN_APPOINTMENT_TIME',
            'connection_type' => 'DIRECT',
            'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 125,
            'raw_payload' => [],
        ]);
        UserWallet::create([
            'user_id' => $patient->id,
            'amount_change' => 50000,
            'balance_before' => 0,
            'balance_after' => 50000,
            'type' => 'credit',
            'idempotency_key' => 'user-report-wallet-1',
        ]);
        FeedBack::create(['appointment_user_id' => $appointment->id, 'question' => 1, 'answer' => 0]);

        $this->get('/admin/user/report/'.$patient->id)
            ->assertOk()
            ->assertSee('گزارش جامع')
            ->assertSee('USER-REPORT-CALL-1')
            ->assertSee('USER-REPORT-1')
            ->assertSee('/admin/online-consultation/call-reports/appointments/'.$appointment->id, false)
            ->assertSee('جزئیات و پرونده')
            ->assertSee('50,000')
            ->assertSee('رضایت مثبت');

        $appointment->delete();
        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertSee('فقط برای مشاهده سوابق')
            ->assertDontSee('ثبت گزارش جدید مشاوره');
    }

    public function test_central_activation_requires_super_admin(): void
    {
        tenancy()->initialized = false;
        $this->putJson('http://central.test/central/online-consultation/test-only', ['enabled' => true])->assertForbidden();
    }

    public function test_central_disabling_changes_only_the_selected_site(): void
    {
        tenancy()->initialized = false;
        config(['tenancy.database.central_connection' => 'sqlite']);
        Schema::create('tenants', function ($table) {
            $table->string('id')->primary();
            $table->json('data')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        DB::table('tenants')->insert([
            ['id' => 'site-a', 'data' => json_encode(['online_consultation_enabled' => true])],
            ['id' => 'site-b', 'data' => json_encode(['online_consultation_enabled' => true])],
        ]);
        $this->manager->givePermissionTo('SUPER_ADMIN');
        $this->putJson('http://central.test/central/online-consultation/site-a', ['enabled' => false])->assertRedirect();
        $this->assertFalse((bool) Tenant::findOrFail('site-a')->online_consultation_enabled);
        $this->assertTrue((bool) Tenant::findOrFail('site-b')->online_consultation_enabled);
        $this->assertFalse((bool) (new Tenant(['id' => 'new-site']))->online_consultation_enabled);
    }

    public function test_practitioner_form_does_not_update_specialty_or_weekly_schedule(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile([
            'specialty' => 'نباید ذخیره شود',
            'weekly_schedule' => [['enabled' => '1', 'start' => '09:00', 'end' => '17:00']],
        ]))->assertSessionHasNoErrors();

        $person = ConsultationPractitioner::firstOrFail();
        $this->assertNull($person->specialty);
        $this->assertNull($person->weekly_schedule);
    }

    public function test_final_confirmation_atomically_credits_wallet_locks_calculation_and_records_actor(): void
    {
        $this->assertSame(333000, app(AppointmentBillingService::class)->amountForMinutes(1000000, 20));
        $this->assertSame(667000, app(AppointmentBillingService::class)->amountForMinutes(1000000, 40));
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        $patient = User::create(['mobile' => '09120000002', 'password' => 'test-password']);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => now()->subHour(),
            'start_time' => '10:00:00', 'end_time' => '11:00:00', 'tracking_code' => 'FINAL-REFUND-TEST',
        ]));
        $billing = app(AppointmentBillingService::class)->ensure($appointment);

        $this->put('/admin/online-consultation/billing/'.$billing->id.'/approve', [
            'approved_unused_minutes' => 30, 'reason' => 'تأیید نهایی تست',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $billing->refresh();
        $wallet = UserWallet::firstOrFail();
        $this->assertSame('completed', $billing->refund_status);
        $this->assertSame(500000, (int) $billing->refunded_amount);
        $this->assertSame($wallet->id, $billing->wallet_transaction_id);
        $this->assertSame($this->manager->id, $billing->approved_by);
        $this->assertSame($appointment->id, data_get($wallet->detail, 'appointment_id'));
        $this->assertSame('FINAL-REFUND-TEST', data_get($wallet->detail, 'appointment_tracking_code'));
        $this->assertSame($this->manager->id, data_get($wallet->detail, 'confirmed_by'));
        $this->assertSame(300000, (int) $billing->practitioner_earned_amount);
        $this->assertSame(200000, (int) $billing->platform_profit_amount);
        $this->assertSame(300000, data_get($wallet->detail, 'practitioner_receivable'));
        $this->assertSame(-30, data_get($wallet->detail, 'minute_difference'));
        $this->assertTrue(data_get($wallet->detail, 'minutes_changed_by_practitioner'));
        $audit = $billing->audits()->where('action', 'practitioner_adjusted_refund')->firstOrFail();
        $this->assertStringContainsString('پزشک', $audit->reason);
        $this->assertStringContainsString('30 دقیقه کاهش', $audit->reason);

        $this->put('/admin/online-consultation/billing/'.$billing->id.'/approve', [
            'approved_unused_minutes' => 40, 'reason' => 'درخواست تکراری',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, UserWallet::count());
        $this->assertSame(30, (int) $billing->fresh()->approved_unused_minutes);

        $this->post('/admin/online-consultation/billing/'.$billing->id.'/correct', [
            'corrected_unused_minutes' => 20, 'reason' => 'اصلاح تستی', 'request_token' => (string) Str::uuid(),
        ])->assertForbidden();
    }

    public function test_financial_split_excludes_short_reconnection_calls_and_calculates_both_profits(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        ConsultationSetting::current()->update(['ignored_short_call_minutes' => 6]);
        $patient = User::create(['mobile' => '09120000022', 'password' => 'test-password']);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => now()->subHour(),
            'start_time' => '10:00:00', 'end_time' => '11:00:00', 'tracking_code' => 'SPLIT-TEST',
        ]));
        foreach ([1800, 360] as $index => $seconds) {
            AppointmentCallLog::create([
                'appointment_id' => $appointment->id,
                'call_id' => 'split-test-'.($index + 1),
                'patient_phone' => $patient->mobile,
                'direction' => 'INBOUND',
                'final_result' => 'ANSWERED',
                'talk_duration_seconds' => $seconds,
                'total_duration_seconds' => $seconds,
                'raw_payload' => [],
            ]);
        }

        $billing = app(AppointmentBillingService::class)->ensure($appointment);

        $this->assertSame(2160, $billing->raw_answered_talk_seconds);
        $this->assertSame(360, $billing->ignored_talk_seconds);
        $this->assertSame(1800, $billing->answered_talk_seconds);
        $this->assertSame(500000, $billing->suggested_refund_amount);
        $this->assertSame(300000, $billing->practitioner_earned_amount);
        $this->assertSame(200000, $billing->platform_profit_amount);
    }

    public function test_cancelled_appointment_is_excluded_and_cannot_be_refunded_while_pending_money_is_not_final_income(): void
    {
        $this->post('/admin/online-consultation/practitioners', $this->profile())->assertSessionHasNoErrors();
        $patient = User::create(['mobile' => '09120000032', 'password' => 'test-password']);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value,
            'type' => 1, 'kind' => 3, 'date_visit' => now()->subHour(),
            'start_time' => '10:00:00', 'end_time' => '11:00:00', 'tracking_code' => 'CANCELLED-FINANCE-TEST',
        ]));
        $billing = app(AppointmentBillingService::class)->ensure($appointment);

        $pendingMetrics = app(ConsultantFinancialReportService::class)->metrics(collect([$appointment->load('billingRecord.adjustments')]));
        $this->assertSame(0, $pendingMetrics['gross_income']);
        $this->assertSame(1000000, $pendingMetrics['pending_gross_income']);
        $this->assertSame(0, $pendingMetrics['effective_refund']);
        $this->assertSame(0, $pendingMetrics['practitioner_income']);
        $this->assertSame(0, $pendingMetrics['platform_profit']);

        $appointment->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL->value]);
        $this->assertNull(app(AppointmentBillingService::class)->ensure($appointment->refresh()));
        $this->put('/admin/online-consultation/billing/'.$billing->id.'/approve', [
            'approved_unused_minutes' => 60,
        ])->assertSessionHasErrors('refund');
        $this->assertSame(0, UserWallet::count());

        $report = app(ConsultantFinancialReportService::class)->report(now()->subDay(), now()->addDay());
        $this->assertFalse($report['appointments']->contains('id', $appointment->id));
        $this->get('/admin/online-consultation/call-reports')->assertOk()->assertDontSee('CANCELLED-FINANCE-TEST');
    }

    public function test_practitioner_can_report_complete_and_reopen_a_consultation_case(): void
    {
        $patient = User::create(['mobile' => '09124445566', 'password' => 'test-password']);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => now(),
            'start_time' => now()->format('H:i:s'), 'end_time' => now()->addMinutes(30)->format('H:i:s'),
            'tracking_code' => 'CASE-FLOW-1',
        ]));

        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertSee('تاریخ و ساعت گزارش')
            ->assertSee('value="'.verta(now('Asia/Tehran'))->format('Y/m/d').'"', false)
            ->assertSee('value="'.now('Asia/Tehran')->format('H:i').'"', false);

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/reports', [
            'outcome' => 'OTHER',
            'subject' => 'گزارش بدون زمان',
            'report_text' => 'این گزارش بدون تاریخ و ساعت نباید ثبت شود.',
        ])->assertSessionHasErrors(['follow_up_date', 'follow_up_time']);

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/reports', [
            'outcome' => 'FOLLOW_UP_REQUIRED',
            'subject' => 'نیاز به بررسی مجدد',
            'report_text' => 'شرح کامل مشاوره و توصیه‌های لازم برای جلسه بعد ثبت شد.',
            'follow_up_date' => '1405/06/25',
            'follow_up_time' => '14:30',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('appointment_consultation_reports', [
            'appointment_id' => $appointment->id, 'author_id' => $this->manager->id,
            'outcome' => 'FOLLOW_UP_REQUIRED', 'subject' => 'نیاز به بررسی مجدد',
        ]);
        $storedFollowUp = \Modules\OnlineConsultation\Models\AppointmentConsultationReport::firstOrFail()->follow_up_at;
        $this->assertSame(
            Verta::parse('1405/06/25')->toCarbon()->setTime(14, 30)->format('Y-m-d H:i'),
            $storedFollowUp->format('Y-m-d H:i')
        );

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/note', [
            'appointment_note' => 'این توضیح واحد برای همین نوبت ثبت شده است.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('appointment_consultation_cases', [
            'appointment_id' => $appointment->id,
            'appointment_note' => 'این توضیح واحد برای همین نوبت ثبت شده است.',
            'note_author_id' => $this->manager->id,
            'note_author_role' => 'PRACTITIONER',
        ]);
        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/note', [
            'appointment_note' => 'توضیح دوم نباید ذخیره شود.',
        ])->assertSessionHasErrors('appointment_note');

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/complete', [])
            ->assertSessionHasErrors('completion_confirmed');
        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/complete', ['completion_confirmed' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('appointment_consultation_cases', ['appointment_id' => $appointment->id, 'state' => 'COMPLETED']);
        $this->assertDatabaseHas('appointment_consultation_case_events', ['action' => 'COMPLETED', 'actor_id' => $this->manager->id]);
        $this->assertTrue($appointment->fresh()->load('consultationCase')->hasCompletedPhoneConsultation());

        $inPersonAppointment = $appointment->replicate(['tracking_code']);
        $inPersonAppointment->kind = \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::IN_PERSION;
        $inPersonAppointment->tracking_code = 'IN-PERSON-COMPLETED-CASE';
        $inPersonAppointment->saveQuietly();
        \Modules\OnlineConsultation\Models\AppointmentConsultationCase::create([
            'appointment_id' => $inPersonAppointment->id,
            'state' => \Modules\OnlineConsultation\Models\AppointmentConsultationCase::STATE_COMPLETED,
        ]);
        $this->assertFalse($inPersonAppointment->fresh()->load('consultationCase')->hasCompletedPhoneConsultation());

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/reports', [
            'outcome' => 'OTHER', 'subject' => 'گزارش بعد از اتمام', 'report_text' => 'این گزارش نباید در پرونده بسته ذخیره شود.',
        ])->assertSessionHasErrors('case');

        $this->post('/admin/online-consultation/call-reports/appointments/'.$appointment->id.'/reopen', [
            'reopen_reason' => 'نیاز بیمار به ادامه مشاوره و ثبت توضیحات تکمیلی', 'reopen_confirmed' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('appointment_consultation_cases', ['appointment_id' => $appointment->id, 'state' => 'OPEN']);
        $this->assertDatabaseHas('appointment_consultation_case_events', ['action' => 'REOPENED', 'actor_id' => $this->manager->id]);

        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()->assertSee('ثبت گزارش جدید مشاوره')->assertSee('نیاز به بررسی مجدد')->assertSee('تاریخچه تغییر وضعیت پرونده');
    }

    public function test_voip_reminder_rules_are_attached_to_the_same_appointment_for_both_recipients(): void
    {
        foreach ([
            ['title' => 'سه ساعت قبل', 'recipient_type' => 'patient', 'offset_value' => 3, 'offset_unit' => 'hour', 'template' => 'patient-180', 'active' => 1],
            ['title' => 'پانزده دقیقه قبل', 'recipient_type' => 'patient', 'offset_value' => 15, 'offset_unit' => 'minute', 'template' => 'patient-15', 'active' => 1],
            ['title' => 'بیست دقیقه قبل', 'recipient_type' => 'practitioner', 'offset_value' => 20, 'offset_unit' => 'minute', 'template' => 'practitioner-20', 'active' => 1],
        ] as $rule) {
            $this->post('/admin/online-consultation/sms-reminders', $rule)->assertSessionHasNoErrors()->assertRedirect();
        }

        $patient = User::create(['mobile' => '09123334455', 'password' => 'test-password', 'first_name' => 'بیمار']);
        $visitAt = now()->addHours(6)->startOfMinute();
        $appointment = AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => $visitAt,
            'start_time' => $visitAt->format('H:i:s'), 'end_time' => $visitAt->copy()->addMinutes(30)->format('H:i:s'),
            'tracking_code' => 'VOIP-REMINDER-1',
        ]);

        $deliveries = ConsultationSmsDelivery::where('appointment_id', $appointment->id)->whereNotNull('reminder_rule_id')->get();
        $this->assertCount(3, $deliveries);
        $this->assertCount(2, $deliveries->where('recipient_type', 'patient'));
        $this->assertCount(1, $deliveries->where('recipient_type', 'practitioner'));
        $this->assertSame($this->manager->mobile, $deliveries->firstWhere('recipient_type', 'practitioner')->recipient);
        $this->assertSame($visitAt->copy()->subMinutes(20)->toDateTimeString(), $deliveries->firstWhere('recipient_type', 'practitioner')->scheduled_at->toDateTimeString());

        $this->get('/admin/online-consultation/sms-reminders?appointment=VOIP-REMINDER-1')
            ->assertOk()->assertSee('راهنمای پارامترهای قالب')->assertSee('VOIP-REMINDER-1')->assertSee('پزشک / مشاور');
    }

    public function test_due_voip_reminder_is_queued_and_cancelled_appointment_is_skipped(): void
    {
        Queue::fake();
        $rule = ConsultationSmsReminderRule::create([
            'title' => 'یادآوری فوری', 'recipient_type' => 'patient', 'minutes_before' => 15,
            'template' => 'patient-now', 'active' => true,
        ]);
        $patient = User::create(['mobile' => '09125556677', 'password' => 'test-password']);
        $appointment = AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => now()->addMinutes(15),
            'start_time' => now()->addMinutes(15)->format('H:i:s'), 'end_time' => now()->addMinutes(45)->format('H:i:s'),
            'tracking_code' => 'VOIP-DUE-1',
        ]);
        $delivery = ConsultationSmsDelivery::where('appointment_id', $appointment->id)->where('reminder_rule_id', $rule->id)->firstOrFail();

        app(ConsultationReminderScheduler::class)->dispatchDue();
        Queue::assertPushed(SendConsultationSms::class, fn ($job) => $job->deliveryId === $delivery->id);
        $this->assertSame('queued', $delivery->fresh()->status);

        $movedVisit = now()->addMinutes(90)->startOfMinute();
        $appointment->update([
            'date_visit' => $movedVisit,
            'start_time' => $movedVisit->format('H:i:s'),
            'end_time' => $movedVisit->copy()->addMinutes(30)->format('H:i:s'),
        ]);
        $delivery->refresh();
        $this->assertSame('pending', $delivery->status);
        $this->assertSame($movedVisit->copy()->subMinutes(15)->toDateTimeString(), $delivery->scheduled_at->toDateTimeString());
        $this->assertSame(verta($movedVisit)->format('H:i'), data_get($delivery->payload, 'params.5'));

        // Even a stale queued job must not send after the appointment has moved.
        $delivery->update(['status' => 'queued']);
        (new SendConsultationSms($delivery->id, tenant()?->getTenantKey()))->handle(app(ConsultationReminderScheduler::class));
        $this->assertSame('pending', $delivery->fresh()->status);

        // A direct database cancellation (which has no model event) is checked again by the sending job.
        DB::table('appointment_users')->where('id', $appointment->id)->update(['status' => 3]);
        $delivery->update(['status' => 'queued', 'scheduled_at' => now()->subMinute()]);
        (new SendConsultationSms($delivery->id, tenant()?->getTenantKey()))->handle(app(ConsultationReminderScheduler::class));
        $this->assertSame('skipped', $delivery->fresh()->status);

        $deletedAppointment = AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => now()->addHour(),
            'start_time' => now()->addHour()->format('H:i:s'), 'end_time' => now()->addMinutes(90)->format('H:i:s'),
            'tracking_code' => 'VOIP-DELETE-1',
        ]);
        $deletedDelivery = ConsultationSmsDelivery::where('appointment_id', $deletedAppointment->id)->where('reminder_rule_id', $rule->id)->firstOrFail();
        $deletedAppointment->delete();
        $this->assertSame('skipped', $deletedDelivery->fresh()->status);
    }

    public function test_early_calls_are_labeled_and_excluded_from_every_unanswered_metric(): void
    {
        $patient = User::create(['mobile' => '09127778899', 'password' => 'test-password']);
        $start = now()->subDay()->setTime(21, 25);
        $end = $start->copy()->setTime(22, 0);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => $start,
            'start_time' => '21:25:00', 'end_time' => '22:00:00',
            'tracking_code' => 'EARLY-CALL-TEST',
        ]));

        $makeCall = function (string $id, $enteredAt, string $state) use ($appointment, $patient, $start, $end) {
            return AppointmentCallLog::create([
                'appointment_id' => $appointment->id,
                'call_id' => $id,
                'patient_phone' => $patient->mobile,
                'direction' => 'INBOUND',
                'appointment_state' => $state,
                'connection_type' => 'NONE',
                'final_result' => 'CALLER_ABANDONED',
                'appointment_start_at' => $start,
                'appointment_end_at' => $end,
                'call_entered_at' => $enteredAt,
                'raw_payload' => [],
            ]);
        };

        // The exact 13-minute-and-9-second gap is retained for display.
        $early = $makeCall('1789062110.232', $start->copy()->subMinutes(13)->subSeconds(9), 'IN_APPOINTMENT_TIME');
        $inWindow = $makeCall('IN-WINDOW-MISSED', $start->copy()->addMinutes(5), 'IN_APPOINTMENT_TIME');
        $late = $makeCall('AFTER-WINDOW', $end->copy()->addMinute(), 'APPOINTMENT_EXPIRED');

        $this->assertTrue($early->isEarlyCall());
        $this->assertSame(13, $early->earlyByMinutes());
        $this->assertSame(789, $early->earlyBySeconds());
        $this->assertFalse($early->countsAsUnanswered());
        $this->assertTrue($inWindow->countsAsUnanswered());
        $this->assertFalse($late->countsAsUnanswered());
        $this->assertSame('info', \Modules\OnlineConsultation\Support\CallResultPresentation::tone($early));
        $this->assertSame('danger', \Modules\OnlineConsultation\Support\CallResultPresentation::tone($inWindow));
        $this->assertSame('تماس 13 دقیقه و 9 ثانیه زودتر از نوبت گرفته شده', \Modules\OnlineConsultation\Support\CallResultPresentation::label($early));
        $this->assertSame('warning', \Modules\OnlineConsultation\Support\CallResultPresentation::rawTone('BUSY'));
        $this->assertSame('purple', \Modules\OnlineConsultation\Support\CallResultPresentation::rawTone('CONGESTION'));

        $appointment->load(['callLogs', 'billingRecord', 'consultationCase']);
        $decorated = app(ConsultantDashboardService::class)->decorate($appointment);
        $this->assertSame(1, $decorated->dashboard['unanswered']);
        $this->assertSame(1, $decorated->dashboard['early_calls']);

        $appointment->load(['callLogs.consultantHangup', 'callLogs.consultantNoAnswer', 'billingRecord.adjustments', 'feedbacks', 'consultationCase']);
        $metrics = app(ConsultantFinancialReportService::class)->metrics(collect([$appointment]));
        $this->assertSame(1, $metrics['unanswered']);
        $this->assertSame(1, $metrics['early_calls']);
        $this->assertSame(1, $metrics['call_results']['EARLY_CALL']);

        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertSee('تماس 13 دقیقه و 9 ثانیه زودتر از نوبت گرفته شده')
            ->assertSee('شناسه فنی تماس: 1789062110.232');
    }

    public function test_consultant_hangup_is_only_a_warning_for_calls_at_or_below_the_configured_short_call_limit(): void
    {
        ConsultationSetting::current()->update(['ignored_short_call_minutes' => 1]);
        $patient = User::create(['mobile' => '09128889900', 'password' => 'test-password']);
        $start = now()->subMinutes(10);
        $appointment = AppointmentUser::withoutEvents(fn () => AppointmentUser::create([
            'user_id' => $patient->id, 'doctor_id' => $this->manager->id,
            'status' => 1, 'type' => 1, 'kind' => 3, 'date_visit' => $start,
            'start_time' => $start->format('H:i:s'), 'end_time' => $start->copy()->addHour()->format('H:i:s'),
            'tracking_code' => 'HANGUP-THRESHOLD',
        ]));

        $makeCall = function (string $callId, int $talkSeconds) use ($appointment, $patient, $start) {
            $call = AppointmentCallLog::create([
                'appointment_id' => $appointment->id, 'call_id' => $callId,
                'patient_phone' => $patient->mobile, 'direction' => 'INBOUND',
                'appointment_state' => 'IN_APPOINTMENT_TIME', 'connection_type' => 'DIRECT',
                'final_result' => 'ANSWERED', 'appointment_start_at' => $start,
                'appointment_end_at' => $start->copy()->addHour(), 'call_entered_at' => $start->copy()->addMinute(),
                'talk_duration_seconds' => $talkSeconds, 'disconnected_by' => 'DOCTOR', 'raw_payload' => [],
            ]);
            AppointmentConsultantHangup::create([
                'appointment_id' => $appointment->id, 'call_id' => $callId,
                'hung_up_at' => $start->copy()->addSeconds($talkSeconds), 'hangup_via' => 'PHONE', 'raw_payload' => [],
            ]);

            return $call->load('consultantHangup');
        };

        $short = $makeCall('SHORT-HANGUP', 60);
        $completed = $makeCall('COMPLETED-HANGUP', 80);

        $this->assertTrue($short->isConsultantHangupWarning(60));
        $this->assertFalse($short->isCompletedConsultantHangup(60));
        $this->assertSame('تماس کوتاه؛ قطع توسط مشاور', \Modules\OnlineConsultation\Support\CallResultPresentation::label($short, 60));
        $this->assertFalse($completed->isConsultantHangupWarning(60));
        $this->assertTrue($completed->isCompletedConsultantHangup(60));
        $this->assertSame('success', \Modules\OnlineConsultation\Support\CallResultPresentation::tone($completed, 60));
        $this->assertSame('مشاوره انجام شد و تماس پایان یافت', \Modules\OnlineConsultation\Support\CallResultPresentation::label($completed, 60));

        $appointment->load(['callLogs.consultantHangup', 'callLogs.consultantNoAnswer', 'billingRecord.adjustments', 'feedbacks', 'consultationCase']);
        $metrics = app(ConsultantFinancialReportService::class)->metrics(collect([$appointment]));
        $this->assertSame(1, $metrics['consultant_hangups']);

        $this->get('/admin/online-consultation/call-reports/appointments/'.$appointment->id)
            ->assertOk()
            ->assertSee('1 تماس کوتاه با قطع مشاور')
            ->assertSee('مشاوره انجام شد و تماس پایان یافت');
    }

}
