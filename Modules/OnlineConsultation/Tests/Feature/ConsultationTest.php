<?php

namespace Modules\OnlineConsultation\Tests\Feature;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\App\Models\UserWallet;
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
            'Modules/AppointmentUser/database/migrations/tenant/2024_03_19_094623_create_appointment_users_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_06_000001_create_online_consultation_tables.php',
            'Modules/User/Database/Migrations/tenant/2026_02_23_181827_create_user_wallets_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000004_create_appointment_call_logs_table.php',
            'Modules/OnlineConsultation/database/migrations/tenant/2026_09_07_000005_create_appointment_billing_records.php',
        ] as $path) {
            (require base_path($path))->up();
        }
        foreach (['appointment_settings', 'services', 'places', 'transactions'] as $table) {
            Schema::create($table, fn ($blueprint) => $blueprint->id());
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('SUPER_ADMIN', 'web');
        Permission::findOrCreate('ADMIN_ACCESS', 'web');
        $this->manager = User::create(['mobile' => '09120000001', 'password' => 'test-password']);
        $this->manager->givePermissionTo(['ADMIN_ACCESS', 'ONLINE_CONSULTATION_MANAGE']);
        tenancy()->tenant = new Tenant(['id' => 'test-only', 'online_consultation_enabled' => true]);
        tenancy()->initialized = true;
        $this->withoutMiddleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class]);
        $this->actingAs($this->manager);
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
        ], $overrides);
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

}
