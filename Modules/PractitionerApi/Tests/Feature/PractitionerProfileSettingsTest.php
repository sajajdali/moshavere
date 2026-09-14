<?php

namespace Modules\PractitionerApi\Tests\Feature;

use Carbon\Carbon;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Modules\Api\Entities\UserDevice;
use Modules\OnlineConsultation\Http\Middleware\EnsureConsultationEnabled;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\PractitionerApi\Http\Middleware\AuthenticatePractitionerApi;
use Modules\Service\app\Models\Service;
use Modules\Place\app\Models\Place;
use Modules\Speciality\app\Models\Speciality;
use Modules\User\Entities\User;
use Modules\User\Notifications\UserMessageNotification;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class PractitionerProfileSettingsTest extends TestCase
{
    private ConsultationPractitioner $practitioner;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        tenancy()->initialized = true;
        $this->withoutMiddleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureConsultationEnabled::class,
            AuthenticatePractitionerApi::class,
            RestrictedDocsAccess::class,
        ]);

        $this->createSchema();
        $user = User::query()->create(['mobile' => '09120000002', 'password' => 'password']);
        $this->practitioner = ConsultationPractitioner::query()->create([
            'user_id' => $user->id,
            'display_name' => 'دکتر سمیرا نوری',
            'kind' => 'doctor',
            'specialty' => 'روان‌شناسی بالینی',
            'active' => true,
            'app_access' => true,
            'availability' => 'offline',
            'extension' => '102',
            'sip_username' => 'advisor102',
            'sip_secret' => 'private-sip-password',
            'voip_host' => 'legacy-per-practitioner.example.test',
            'duration_minutes' => 30,
            'hourly_rate' => 800000,
            'payout_hourly_rate' => 600000,
        ]);
        ConsultationSetting::query()->create([
            'id' => 1,
            'booking_enabled' => true,
            'app_enabled' => true,
            'duration_minutes' => 20,
            'timezone' => 'Asia/Tehran',
            'voip_host' => 'https://voip.example.test:2214',
            'voip_port' => 5061,
            'voip_transport' => 'tls',
            'voip_call_token' => 'test-call-token',
        ]);
        $specialty = Speciality::query()->create(['title' => 'روان‌شناسی بالینی', 'active' => 1]);
        $place = Place::query()->create(['title' => 'کلینیک مرکزی', 'active' => 1]);
        $user->specialities()->attach($specialty->id);
        $user->places()->attach($place->id);

        Sanctum::actingAs($user, ['practitioner-app']);
        UserDevice::query()->create([
            'user_id' => $user->id, 'access_token_id' => 1001, 'type' => 'android',
            'device_info' => ['device_identifier' => 'profile-test-device'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        tenancy()->initialized = false;
        parent::tearDown();
    }

    public function test_landing_and_completed_profile_are_returned(): void
    {
        $this->assertSame(30, (int) DB::table('consultation_practitioners')->where('id', $this->practitioner->id)->value('duration_minutes'));
        $this->getJson('/api/practitioner/v1/landing/content')
            ->assertOk()
            ->assertJsonPath('data.app_subtitle', 'پنل پزشکان و مشاوران')
            ->assertJsonCount(3, 'data.features');

        $this->getJson('/api/practitioner/v1/me')
            ->assertOk()
            ->assertJsonPath('data.display_name', 'دکتر سمیرا نوری')
            ->assertJsonPath('data.initials', 'سن')
            ->assertJsonPath('data.specialties.0.title', 'روان‌شناسی بالینی')
            ->assertJsonPath('data.activity_centers.0.title', 'کلینیک مرکزی')
            ->assertJsonPath('data.availability', 'offline')
            ->assertJsonPath('data.booking_enabled', true)
            ->assertJsonPath('data.softphone.configured', true)
            ->assertJsonPath('data.softphone.server_address', 'https://voip.example.test:2214')
            ->assertJsonPath('data.softphone.server_host', 'voip.example.test')
            ->assertJsonPath('data.softphone.server_port', 5061)
            ->assertJsonPath('data.softphone.transport', 'tls')
            ->assertJsonPath('data.softphone.extension', '102')
            ->assertJsonPath('data.softphone.username', 'advisor102')
            ->assertJsonPath('data.softphone.password', 'private-sip-password')
            ->assertJsonPath('data.softphone.missing_fields', [])
            ->assertJsonPath('data.default_duration_minutes', 30)
            ->assertJsonPath('data.patient_hourly_rate', 800000)
            ->assertJsonPath('data.practitioner_hourly_rate', 600000)
            ->assertJsonStructure(['data' => ['server_time']]);
    }

    public function test_availability_accepts_only_project_states(): void
    {
        $this->patchJson('/api/practitioner/v1/me/availability', ['availability' => 'ready'])
            ->assertOk()
            ->assertJsonPath('data.availability', 'ready');
        $this->assertSame('ready', $this->practitioner->fresh()->availability);

        $this->patchJson('/api/practitioner/v1/me/availability', ['availability' => 'available'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('availability');
    }

    public function test_softphone_reports_missing_configuration_and_never_uses_practitioner_host(): void
    {
        ConsultationSetting::query()->whereKey(1)->update(['voip_host' => null]);
        $this->practitioner->update(['sip_username' => null, 'sip_secret' => null]);

        $this->getJson('/api/practitioner/v1/me')
            ->assertOk()
            ->assertJsonPath('data.softphone.configured', false)
            ->assertJsonPath('data.softphone.server_address', null)
            ->assertJsonPath('data.softphone.server_host', null)
            ->assertJsonPath('data.softphone.password', null)
            ->assertJsonPath('data.softphone.missing_fields', ['server_host', 'username', 'password']);
    }

    public function test_settings_are_defaulted_and_only_allow_known_boolean_keys(): void
    {
        $this->getJson('/api/practitioner/v1/settings')
            ->assertOk()
            ->assertJsonCount(4, 'data.toggles');

        $this->patchJson('/api/practitioner/v1/settings', ['key' => 'ring_sound', 'value' => false])
            ->assertOk()
            ->assertJsonPath('data.key', 'ring_sound')
            ->assertJsonPath('data.value', false);
        $this->assertNotNull(DB::table('consultation_practitioners')->where('id', $this->practitioner->id)->value('app_settings'));
        $this->assertFalse($this->practitioner->fresh()->app_settings['ring_sound']);

        $this->patchJson('/api/practitioner/v1/settings', ['key' => 'unknown', 'value' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('key');
        $this->patchJson('/api/practitioner/v1/settings', ['key' => 'ring_sound', 'value' => 'yes'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('value');

        $this->patchJson('/api/practitioner/v1/settings', [
            'key' => 'push_calls',
            'value' => false,
            'app_access' => false,
            'hourly_rate' => 1,
            'unknown' => 'must-not-be-persisted',
        ])->assertOk();
        $fresh = $this->practitioner->fresh();
        $this->assertTrue($fresh->app_access);
        $this->assertSame(800000, $fresh->hourly_rate);
        $this->assertSame([
            'ring_sound' => false,
            'push_calls' => false,
        ], $fresh->app_settings);
    }

    public function test_landing_content_can_be_overridden_per_tenant(): void
    {
        ConsultationSetting::query()->whereKey(1)->update([
            'app_landing_content' => json_encode([
                'headline' => 'عنوان اختصاصی مرکز',
                'features' => [['order' => 1, 'title' => 'ویژگی اختصاصی', 'body' => 'توضیح']],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $this->getJson('/api/practitioner/v1/landing/content')
            ->assertOk()
            ->assertJsonPath('data.headline', 'عنوان اختصاصی مرکز')
            ->assertJsonPath('data.features.0.title', 'ویژگی اختصاصی')
            ->assertJsonPath('data.cta_label', 'ورود به پنل پزشک');
    }

    public function test_sections_are_built_from_existing_appointment_settings(): void
    {
        $service = Service::query()->create(['title' => 'مشاوره خانواده', 'active' => 1]);
        $settingId = DB::table('appointment_settings')->insertGetId([
            'user_id' => $this->practitioner->user_id,
            'service_id' => $service->id,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('appointment_setting_times')->insert([
            'appointment_setting_id' => $settingId,
            'day_number' => 0,
            'start_at' => '09:00:00',
            'end_at' => '17:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/me/sections')
            ->assertOk()
            ->assertJsonPath('data.editable', false)
            ->assertJsonPath('data.sections.0.key', 'service:'.$service->id)
            ->assertJsonPath('data.sections.0.name', 'مشاوره خانواده')
            ->assertJsonPath('data.sections.0.hours.0.from', '09:00')
            ->assertJsonPath('data.sections.0.hours.0.closed', false)
            ->assertJsonPath('data.sections.0.hours.6.closed', true);
    }

    public function test_completed_routes_are_exposed_in_openapi(): void
    {
        $document = $this->getJson('/docs/practitioner/openapi.json')
            ->assertOk()
            ->json();
        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertGreaterThanOrEqual(24, count($document['paths']));
        $this->assertArrayHasKey('http', $document['components']['securitySchemes']);
        foreach ($document['paths'] as $operations) {
            foreach ($operations as $operation) {
                if (! is_array($operation) || ! isset($operation['operationId'])) continue;
                $this->assertNotEmpty($operation['responses'] ?? [], $operation['operationId'].' has no documented response.');
            }
        }

        foreach (['/me', '/me/sections', '/me/availability', '/landing/content', '/settings'] as $path) {
            $this->assertArrayHasKey($path, $document['paths']);
        }

        $profileSchema = $document['paths']['/me']['get']['responses'][200]['content']['application/json']['schema']['properties']['data']['properties'];
        $this->assertArrayHasKey('specialties', $profileSchema);
        $this->assertArrayHasKey('activity_centers', $profileSchema);
        $this->assertArrayHasKey('softphone', $profileSchema);
        $this->assertArrayHasKey('password', $profileSchema['softphone']['properties']);
        $this->assertArrayHasKey('/dashboard', $document['paths']);
        $this->assertArrayHasKey('/appointments', $document['paths']);
        $this->assertArrayHasKey('/appointments/{appointment}', $document['paths']);
        foreach (['/appointments/{appointment}/calls', '/appointments/{appointment}/auto-call', '/calls/active', '/calls/{call}/note', '/voip/config'] as $path) {
            $this->assertArrayHasKey($path, $document['paths']);
        }
        foreach (['/appointments/{appointment}/reports', '/patients/{patient}/history'] as $path) {
            $this->assertArrayHasKey($path, $document['paths']);
        }
        $this->assertArrayHasKey('/appointments/{appointment}/complete', $document['paths']);
        $this->assertArrayHasKey('/appointments/{appointment}/no-show', $document['paths']);
        $this->assertArrayHasKey('/appointments/{appointment}/settlement', $document['paths']);
        $this->assertArrayHasKey('/reports/daily', $document['paths']);
        $this->assertArrayHasKey('/devices/current', $document['paths']);

        $loginPractitionerSchema = $document['paths']['/auth/otp/verify']['post']['responses'][200]['content']['application/json']['schema']['properties']['data']['properties']['practitioner']['properties'];
        $this->assertArrayHasKey('softphone', $loginPractitionerSchema);
        $this->assertArrayHasKey('server_host', $loginPractitionerSchema['softphone']['properties']);
        $this->assertArrayHasKey('username', $loginPractitionerSchema['softphone']['properties']);
        $this->assertArrayHasKey('password', $loginPractitionerSchema['softphone']['properties']);
    }

    public function test_dashboard_returns_owned_upcoming_appointment_stats_and_countdown(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 10:00:00', 'Asia/Tehran'));
        $patient = User::query()->create(['mobile' => '09125550001', 'password' => 'password']);
        $service = Service::query()->create(['title' => 'مشاوره خانواده', 'active' => 1]);
        DB::table('appointment_users')->insert([
            'user_id' => $patient->id,
            'doctor_id' => $this->practitioner->user_id,
            'service_id' => $service->id,
            'kind' => 3,
            'status' => 1,
            'tracking_code' => 'DASH-1001',
            'date_visit' => '2026-09-13 11:00:00',
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'details' => json_encode(['description' => 'پیگیری درمان'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/dashboard?date=2026-09-13')
            ->assertOk()
            ->assertJsonPath('data.timezone', 'Asia/Tehran')
            ->assertJsonPath('data.practitioner.id', $this->practitioner->id)
            ->assertJsonPath('data.booking.enabled', true)
            ->assertJsonPath('data.voip.configured', true)
            ->assertJsonPath('data.next_appointment.file_no', 'DASH-1001')
            ->assertJsonPath('data.next_appointment.countdown.starts_in_seconds', 3600)
            ->assertJsonPath('data.next_appointment.complaint_summary', 'پیگیری درمان')
            ->assertJsonPath('data.today_stats.total', 1)
            ->assertJsonPath('data.today_stats.scheduled', 1)
            ->assertJsonPath('data.today_stats.booked_minutes', 30)
            ->assertJsonPath('data.empty_state.show', false)
            ->assertJsonCount(7, 'data.week_strip');
    }

    public function test_dashboard_has_explicit_empty_state_and_validates_date(): void
    {
        $this->getJson('/api/practitioner/v1/dashboard?date=2026-09-14')
            ->assertOk()
            ->assertJsonPath('data.next_appointment', null)
            ->assertJsonPath('data.upcoming_today', [])
            ->assertJsonPath('data.today_stats.total', 0)
            ->assertJsonPath('data.financial_summary.currency', 'TOMAN')
            ->assertJsonPath('data.empty_state.show', true);

        $this->getJson('/api/practitioner/v1/dashboard?date=1405/06/22')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function test_appointment_list_filters_searches_and_paginates_only_owned_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 10:00:00', 'Asia/Tehran'));
        $today = $this->createAppointment('09125550111', '2026-09-13 11:00:00', 'OWN-TODAY');
        $this->createAppointment('09351112233', '2026-09-14 12:00:00', 'OWN-TOMORROW');
        $this->createAppointment('09999999999', '2026-09-13 13:00:00', 'CANCELLED', 3);

        $this->getJson('/api/practitioner/v1/appointments?scope=today&per_page=1')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $today)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure(['links' => ['first', 'last', 'prev', 'next'], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);

        $this->getJson('/api/practitioner/v1/appointments?scope=tomorrow')
            ->assertOk()->assertJsonPath('data.0.file_no', 'OWN-TOMORROW');
        $this->getJson('/api/practitioner/v1/appointments?scope=all&date=2026-09-14')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_no', 'OWN-TOMORROW');
        $this->getJson('/api/practitioner/v1/appointments?scope=all&q=۰۹۳۵۱۱۱۲۲۳۳')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_no', 'OWN-TOMORROW');
    }

    public function test_appointment_detail_returns_actions_and_hides_other_practitioners_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 10:00:00', 'Asia/Tehran'));
        $owned = $this->createAppointment('09125550111', '2026-09-13 11:00:00', 'OWN-DETAIL');
        $otherUser = User::query()->create(['mobile' => '09120000999', 'password' => 'password']);
        ConsultationPractitioner::query()->create(['user_id' => $otherUser->id, 'display_name' => 'پزشک دیگر', 'kind' => 'doctor', 'active' => true, 'app_access' => true, 'availability' => 'ready']);
        $otherPatient = User::query()->create(['mobile' => '09127770000', 'password' => 'password']);
        $other = DB::table('appointment_users')->insertGetId([
            'user_id' => $otherPatient->id, 'doctor_id' => $otherUser->id, 'kind' => 3, 'status' => 1,
            'tracking_code' => 'OTHER', 'date_visit' => '2026-09-13 11:00:00', 'start_time' => '11:00:00', 'end_time' => '11:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/appointments/'.$owned)
            ->assertOk()
            ->assertJsonPath('data.file_no', 'OWN-DETAIL')
            ->assertJsonPath('data.actions.complete.allowed', false)
            ->assertJsonPath('data.actions.complete.reason_code', 'report_required')
            ->assertJsonPath('data.actions.auto_call.reason_code', 'too_early')
            ->assertJsonPath('data.actions.settlement.reason_code', 'appointment_not_ended')
            ->assertJsonPath('data.settlement.currency', 'TOMAN');

        $this->getJson('/api/practitioner/v1/appointments/'.$other)->assertNotFound();
        $this->getJson('/api/practitioner/v1/appointments/not-a-number')->assertNotFound();
    }

    public function test_appointment_actions_change_with_reports_time_and_answered_calls(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 12:00:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550111', '2026-09-13 09:00:00', 'PAST-ACTIONS');
        DB::table('appointment_consultation_reports')->insert([
            'appointment_id' => $appointment, 'author_id' => $this->practitioner->user_id,
            'outcome' => 'SUCCESSFUL', 'title' => 'گزارش تست', 'body' => 'انجام شد',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/appointments/'.$appointment)
            ->assertOk()
            ->assertJsonPath('data.status', 'missed')
            ->assertJsonPath('data.actions.complete.allowed', true)
            ->assertJsonPath('data.actions.no_show.allowed', true)
            ->assertJsonPath('data.actions.auto_call.reason_code', 'appointment_ended')
            ->assertJsonPath('data.actions.settlement.allowed', true);

        DB::table('appointment_call_logs')->insert([
            'appointment_id' => $appointment, 'direction' => 'INBOUND', 'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 600, 'call_entered_at' => '2026-09-13 09:05:00',
            'appointment_start_at' => '2026-09-13 09:00:00', 'appointment_end_at' => '2026-09-13 09:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/appointments/'.$appointment)
            ->assertOk()
            ->assertJsonPath('data.actions.no_show.allowed', false)
            ->assertJsonPath('data.actions.no_show.reason_code', 'answered_call_in_window')
            ->assertJsonPath('data.call_stats.answered', 1)
            ->assertJsonPath('data.call_stats.talk_seconds', 600);
    }

    public function test_call_history_active_call_and_note_are_scoped_to_current_practitioner(): void
    {
        $appointment = $this->createAppointment('09125550112', '2026-09-13 11:00:00', 'CALLS');
        $call = DB::table('appointment_call_logs')->insertGetId([
            'appointment_id' => $appointment, 'direction' => 'OUTBOUND', 'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 420, 'call_entered_at' => '2026-09-13 11:07:00',
            'answered_at' => '2026-09-13 11:07:05', 'ended_at' => null,
            'appointment_start_at' => '2026-09-13 11:00:00', 'appointment_end_at' => '2026-09-13 11:30:00',
            'connection_type' => 'VOIP', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_call_logs')->insert([
            'appointment_id' => $appointment, 'direction' => 'INBOUND', 'final_result' => 'NO_ANSWER',
            'talk_duration_seconds' => 0, 'call_entered_at' => '2026-09-13 11:03:00',
            'ended_at' => '2026-09-13 11:03:20', 'appointment_start_at' => '2026-09-13 11:00:00',
            'appointment_end_at' => '2026-09-13 11:30:00', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/appointments/'.$appointment.'/calls')
            ->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('meta.answered', 1)
            ->assertJsonPath('meta.missed', 1)->assertJsonPath('data.1.id', $call);
        $this->getJson('/api/practitioner/v1/calls/active')
            ->assertOk()->assertJsonPath('data.id', $call)->assertJsonPath('data.appointment_id', $appointment);
        $this->putJson('/api/practitioner/v1/calls/'.$call.'/note', ['note' => 'پیگیری نسخه بیمار'])
            ->assertOk()->assertJsonPath('data.note', 'پیگیری نسخه بیمار');

        $otherUser = User::query()->create(['mobile' => '09121119999', 'password' => 'password']);
        $otherPatient = User::query()->create(['mobile' => '09121118888', 'password' => 'password']);
        $otherAppointment = DB::table('appointment_users')->insertGetId([
            'user_id' => $otherPatient->id, 'doctor_id' => $otherUser->id, 'kind' => 3, 'status' => 1,
            'date_visit' => '2026-09-13 11:00:00', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $otherCall = DB::table('appointment_call_logs')->insertGetId([
            'appointment_id' => $otherAppointment, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson('/api/practitioner/v1/appointments/'.$otherAppointment.'/calls')->assertNotFound();
        $this->putJson('/api/practitioner/v1/calls/'.$otherCall.'/note', ['note' => 'غیرمجاز'])->assertNotFound();
    }

    public function test_voip_configuration_can_update_only_practitioner_credentials(): void
    {
        $this->getJson('/api/practitioner/v1/voip/config')
            ->assertOk()->assertJsonPath('data.server_host', 'voip.example.test')
            ->assertJsonPath('data.password', 'private-sip-password');

        $this->putJson('/api/practitioner/v1/voip/config', [
            'extension' => '205', 'username' => 'advisor205', 'password' => 'new-secret',
            'server_host' => 'attacker.example.test',
        ])->assertOk()->assertJsonPath('data.extension', '205')
            ->assertJsonPath('data.server_host', 'voip.example.test')->assertJsonPath('data.password', 'new-secret');

        $this->putJson('/api/practitioner/v1/voip/config', ['extension' => '205', 'username' => 'advisor-renamed'])
            ->assertOk()->assertJsonPath('data.password', 'new-secret');
    }

    public function test_auto_call_requires_timing_and_accepts_a_single_recent_request(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 11:06:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550113', '2026-09-13 11:00:00', 'AUTO-CALL');
        Http::fake(['*' => Http::response(['accepted' => true], 202)]);

        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/auto-call')
            ->assertStatus(202)->assertJsonPath('data.appointment_id', $appointment)
            ->assertJsonPath('data.state', 'accepted')->assertJsonPath('data.extension', '102');
        Http::assertSent(fn ($request) => $request->url() === 'https://voip.example.test:2214/api/v1/VoIP/request_call'
            && $request->hasHeader('X-Call-Fire-Key', 'test-call-token'));
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/auto-call')->assertStatus(409);

        Carbon::setTestNow(Carbon::parse('2026-09-13 11:04:00', 'Asia/Tehran'));
        $early = $this->createAppointment('09125550114', '2026-09-13 11:00:00', 'EARLY-CALL');
        $this->postJson('/api/practitioner/v1/appointments/'.$early.'/auto-call')->assertStatus(409);
    }

    public function test_auto_call_maps_external_service_failure_to_503(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 11:06:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550115', '2026-09-13 11:00:00', 'FAILED-CALL');
        Http::fake(['*' => Http::response(['message' => 'offline'], 500)]);

        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/auto-call')
            ->assertStatus(503)->assertJsonPath('message', 'سرور تماس وضعیت HTTP 500 برگرداند؛ فقط پاسخ HTTP 202 به معنی ثبت درخواست تماس است.');
        $this->assertDatabaseHas('appointment_callback_requests', [
            'appointment_id' => $appointment, 'status' => 'FAILED', 'http_status' => 500,
        ]);
    }

    public function test_reports_can_be_created_listed_and_are_rejected_for_closed_or_foreign_cases(): void
    {
        $appointment = $this->createAppointment('09125550116', '2026-09-13 11:00:00', 'REPORTS');
        $payload = [
            'outcome' => 'FOLLOW_UP_REQUIRED', 'subject' => 'پیگیری روند درمان',
            'report_text' => 'بیمار باید روند درمان را در جلسه آینده پیگیری کند.',
            'follow_up_at' => '2026-09-20T12:30:00+03:30',
        ];

        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/reports', $payload)
            ->assertCreated()->assertJsonPath('data.outcome', 'FOLLOW_UP_REQUIRED')
            ->assertJsonPath('data.subject', 'پیگیری روند درمان')
            ->assertJsonPath('data.author.id', $this->practitioner->user_id);
        $this->getJson('/api/practitioner/v1/appointments/'.$appointment.'/reports')
            ->assertOk()->assertJsonPath('data.case.state', 'OPEN')
            ->assertJsonCount(1, 'data.reports')->assertJsonCount(8, 'data.outcomes');

        DB::table('appointment_consultation_cases')->where('appointment_id', $appointment)->update(['state' => 'COMPLETED']);
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/reports', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('case');
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/reports', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['outcome', 'subject', 'report_text', 'follow_up_at']);

        $otherDoctor = User::query()->create(['mobile' => '09121117777', 'password' => 'password']);
        $otherAppointment = DB::table('appointment_users')->insertGetId([
            'user_id' => DB::table('appointment_users')->where('id', $appointment)->value('user_id'),
            'doctor_id' => $otherDoctor->id, 'kind' => 3, 'status' => 1,
            'date_visit' => '2026-09-14 11:00:00', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson('/api/practitioner/v1/appointments/'.$otherAppointment.'/reports')->assertNotFound();
        $this->postJson('/api/practitioner/v1/appointments/'.$otherAppointment.'/reports', $payload)->assertNotFound();
    }

    public function test_patient_history_contains_only_current_practitioners_relationship(): void
    {
        $appointment = $this->createAppointment('09125550117', '2026-09-13 11:00:00', 'HISTORY');
        $patientId = (int) DB::table('appointment_users')->where('id', $appointment)->value('user_id');
        $caseId = DB::table('appointment_consultation_cases')->insertGetId([
            'appointment_id' => $appointment, 'state' => 'OPEN', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_consultation_reports')->insert([
            'case_id' => $caseId, 'appointment_id' => $appointment, 'author_id' => $this->practitioner->user_id,
            'outcome' => 'SUCCESSFUL', 'subject' => 'گزارش سابقه', 'report_text' => 'شرح کامل گزارش سابقه بیمار',
            'follow_up_at' => '2026-09-20 10:00:00', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/practitioner/v1/patients/'.$patientId.'/history')
            ->assertOk()->assertJsonPath('data.patient.id', $patientId)
            ->assertJsonPath('data.appointments.0.id', $appointment)
            ->assertJsonPath('data.appointments.0.reports.0.subject', 'گزارش سابقه');

        $stranger = User::query()->create(['mobile' => '09125550118', 'password' => 'password']);
        $this->getJson('/api/practitioner/v1/patients/'.$stranger->id.'/history')->assertNotFound();
    }

    public function test_complete_requires_confirmation_and_report_and_is_idempotent(): void
    {
        $appointment = $this->createAppointment('09125550119', '2026-09-13 11:00:00', 'COMPLETE');
        $url = '/api/practitioner/v1/appointments/'.$appointment.'/complete';

        $this->postJson($url)->assertUnprocessable()->assertJsonValidationErrors('completion_confirmed');
        $this->postJson($url, ['completion_confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('completion');

        $caseId = DB::table('appointment_consultation_cases')->insertGetId([
            'appointment_id' => $appointment, 'state' => 'OPEN', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_consultation_reports')->insert([
            'case_id' => $caseId, 'appointment_id' => $appointment, 'author_id' => $this->practitioner->user_id,
            'outcome' => 'SUCCESSFUL', 'subject' => 'گزارش نهایی', 'report_text' => 'گزارش کامل برای پایان مشاوره',
            'follow_up_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->postJson($url, ['completion_confirmed' => true])->assertOk()
            ->assertJsonPath('data.case_state', 'COMPLETED')->assertJsonPath('data.idempotent', false)
            ->assertJsonPath('data.appointment.id', $appointment)
            ->assertJsonPath('data.appointment.actions.complete.allowed', false);
        $this->postJson($url, ['completion_confirmed' => true])->assertOk()
            ->assertJsonPath('data.idempotent', true);
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/no-show', ['no_show_confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('no_show');
        $this->assertSame(1, DB::table('appointment_consultation_case_events')
            ->where('case_id', $caseId)->where('action', 'COMPLETED')->count());
    }

    public function test_no_show_enforces_confirmation_time_and_calls_then_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 12:00:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550120', '2026-09-14 10:00:00', 'NO-SHOW');
        $patientId = (int) DB::table('appointment_users')->where('id', $appointment)->value('user_id');
        $url = '/api/practitioner/v1/appointments/'.$appointment.'/no-show';

        $this->postJson($url)->assertUnprocessable()->assertJsonValidationErrors('no_show_confirmed');
        DB::table('appointment_call_logs')->insert([
            'appointment_id' => $appointment, 'direction' => 'INBOUND', 'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 60, 'call_entered_at' => '2026-09-14 10:05:00',
            'appointment_start_at' => '2026-09-14 10:00:00', 'appointment_end_at' => '2026-09-14 10:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->postJson($url, ['no_show_confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('no_show');
        DB::table('appointment_call_logs')->where('appointment_id', $appointment)->delete();

        DB::table('appointment_billing_records')->insert([
            'appointment_id' => $appointment, 'patient_id' => $patientId,
            'practitioner_id' => $this->practitioner->user_id, 'consultation_type' => 'voip',
            'hourly_rate_snapshot' => 800000, 'payout_hourly_rate_snapshot' => 600000,
            'reserved_minutes' => 30, 'connection_overhead_minutes_snapshot' => 6,
            'total_paid_amount' => 1000000, 'refund_status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->postJson($url, ['no_show_confirmed' => true])->assertOk()
            ->assertJsonPath('data.case_state', 'PATIENT_NO_SHOW')->assertJsonPath('data.idempotent', false)
            ->assertJsonPath('data.appointment.id', $appointment)
            ->assertJsonPath('data.appointment.actions.no_show.allowed', false);
        $this->postJson($url, ['no_show_confirmed' => true])->assertOk()
            ->assertJsonPath('data.idempotent', true);
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/complete', ['completion_confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('completion');
        $caseId = DB::table('appointment_consultation_cases')->where('appointment_id', $appointment)->value('id');
        $this->assertSame(1, DB::table('appointment_consultation_case_events')
            ->where('case_id', $caseId)->where('action', 'PATIENT_NO_SHOW')->count());
        $this->assertDatabaseHas('appointment_billing_records', [
            'appointment_id' => $appointment, 'refund_status' => 'completed',
            'refunded_amount' => 0, 'practitioner_earned_amount' => 300000,
        ]);
    }

    public function test_no_show_is_rejected_before_appointment_end_and_foreign_actions_are_hidden(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 10:15:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550121', '2026-09-14 10:00:00', 'EARLY-NO-SHOW');
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/no-show', ['no_show_confirmed' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('no_show');

        $otherDoctor = User::query()->create(['mobile' => '09125550122', 'password' => 'password']);
        DB::table('appointment_users')->where('id', $appointment)->update(['doctor_id' => $otherDoctor->id]);
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/complete', ['completion_confirmed' => true])->assertNotFound();
        $this->postJson('/api/practitioner/v1/appointments/'.$appointment.'/no-show', ['no_show_confirmed' => true])->assertNotFound();
    }

    public function test_settlement_calculates_refund_wallet_and_income_and_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 12:00:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550123', '2026-09-14 10:00:00', 'SETTLEMENT');
        $patientId = (int) DB::table('appointment_users')->where('id', $appointment)->value('user_id');
        DB::table('appointment_call_logs')->insert([
            'appointment_id' => $appointment, 'direction' => 'INBOUND', 'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 600, 'call_entered_at' => '2026-09-14 10:05:00',
            'appointment_start_at' => '2026-09-14 10:00:00', 'appointment_end_at' => '2026-09-14 10:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_billing_records')->insert([
            'appointment_id' => $appointment, 'patient_id' => $patientId,
            'practitioner_id' => $this->practitioner->user_id, 'consultation_type' => 'voip',
            'hourly_rate_snapshot' => 800000, 'payout_hourly_rate_snapshot' => 600000,
            'reserved_minutes' => 30, 'connection_overhead_minutes_snapshot' => 6,
            'total_paid_amount' => 1000000, 'refund_status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $url = '/api/practitioner/v1/appointments/'.$appointment.'/settlement';

        $this->getJson($url)->assertOk()
            ->assertJsonPath('data.raw_talk_seconds', 600)->assertJsonPath('data.billable_talk_seconds', 960)
            ->assertJsonPath('data.system_unused_minutes', 14)->assertJsonPath('data.suggested_refund_amount', 467000)
            ->assertJsonPath('data.can_confirm', true)->assertJsonPath('data.currency', 'TOMAN');
        $this->postJson($url, ['approved_unused_minutes' => 14])->assertUnprocessable()
            ->assertJsonValidationErrors('settlement_confirmed');
        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 13])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->assertDatabaseMissing('appointment_billing_records', ['appointment_id' => $appointment, 'refund_status' => 'completed']);

        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 14])->assertOk()
            ->assertJsonPath('data.idempotent', false)->assertJsonPath('data.settlement.finalized', true)
            ->assertJsonPath('data.settlement.effective_refund_amount', 467000)
            ->assertJsonPath('data.settlement.practitioner_earned_amount', 160000)
            ->assertJsonPath('data.settlement.platform_profit_amount', 373000)
            ->assertJsonPath('data.appointment.id', $appointment);
        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 14])->assertOk()
            ->assertJsonPath('data.idempotent', true);
        $this->assertDatabaseCount('user_wallets', 1);
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $patientId, 'amount_change' => 467000,
            'idempotency_key' => 'appointment-consultation-refund:1',
        ]);
    }

    public function test_settlement_rejects_early_excessive_and_foreign_requests(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 10:15:00', 'Asia/Tehran'));
        $appointment = $this->createAppointment('09125550124', '2026-09-14 10:00:00', 'EARLY-SETTLEMENT');
        $url = '/api/practitioner/v1/appointments/'.$appointment.'/settlement';
        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('settlement');

        Carbon::setTestNow(Carbon::parse('2026-09-14 12:00:00', 'Asia/Tehran'));
        $patientId = (int) DB::table('appointment_users')->where('id', $appointment)->value('user_id');
        DB::table('appointment_billing_records')->insert([
            'appointment_id' => $appointment, 'patient_id' => $patientId,
            'practitioner_id' => $this->practitioner->user_id, 'consultation_type' => 'voip',
            'hourly_rate_snapshot' => 800000, 'payout_hourly_rate_snapshot' => 600000,
            'reserved_minutes' => 30, 'total_paid_amount' => 1000000, 'refund_status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 31, 'reason' => 'آزمون'])
            ->assertUnprocessable()->assertJsonValidationErrors('approved_unused_minutes');
        $otherDoctor = User::query()->create(['mobile' => '09125550125', 'password' => 'password']);
        DB::table('appointment_users')->where('id', $appointment)->update(['doctor_id' => $otherDoctor->id]);
        $this->getJson($url)->assertNotFound();
        $this->postJson($url, ['settlement_confirmed' => true, 'approved_unused_minutes' => 0])->assertNotFound();
    }

    public function test_daily_report_returns_today_specific_date_and_complete_seven_day_series(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 18:00:00', 'Asia/Tehran'));
        $today = $this->createAppointment('09125550126', '2026-09-14 10:00:00', 'DAILY-TODAY');
        $older = $this->createAppointment('09125550127', '2026-09-10 10:00:00', 'DAILY-OLDER');
        foreach ([[$today, 'COMPLETED'], [$older, 'PATIENT_NO_SHOW']] as [$appointment, $state]) {
            DB::table('appointment_consultation_cases')->insert([
                'appointment_id' => $appointment, 'state' => $state, 'completed_at' => now(),
                'completed_by' => $this->practitioner->user_id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $todayPatient = (int) DB::table('appointment_users')->where('id', $today)->value('user_id');
        $olderPatient = (int) DB::table('appointment_users')->where('id', $older)->value('user_id');
        DB::table('appointment_call_logs')->insert([
            'appointment_id' => $today, 'direction' => 'INBOUND', 'final_result' => 'ANSWERED',
            'talk_duration_seconds' => 600, 'call_entered_at' => '2026-09-14 10:05:00',
            'appointment_start_at' => '2026-09-14 10:00:00', 'appointment_end_at' => '2026-09-14 10:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_billing_records')->insert([
            [
                'appointment_id' => $today, 'patient_id' => $todayPatient, 'practitioner_id' => $this->practitioner->user_id,
                'consultation_type' => 'voip', 'hourly_rate_snapshot' => 800000, 'payout_hourly_rate_snapshot' => 600000,
                'reserved_minutes' => 30, 'connection_overhead_minutes_snapshot' => 6, 'total_paid_amount' => 1000000,
                'refunded_amount' => 467000, 'approved_unused_minutes' => 14, 'refund_status' => 'completed',
                'practitioner_earned_amount' => 160000, 'platform_profit_amount' => 373000, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'appointment_id' => $older, 'patient_id' => $olderPatient, 'practitioner_id' => $this->practitioner->user_id,
                'consultation_type' => 'voip', 'hourly_rate_snapshot' => 800000, 'payout_hourly_rate_snapshot' => 600000,
                'reserved_minutes' => 30, 'connection_overhead_minutes_snapshot' => null,
                'total_paid_amount' => 1000000, 'refunded_amount' => 0, 'approved_unused_minutes' => 0,
                'refund_status' => 'pending', 'practitioner_earned_amount' => 0, 'platform_profit_amount' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/practitioner/v1/reports/daily')->assertOk()
            ->assertJsonPath('data.scope', 'today')->assertJsonPath('data.from', '2026-09-14')
            ->assertJsonPath('data.totals.appointments', 1)->assertJsonPath('data.totals.calls', 1)
            ->assertJsonPath('data.totals.answered', 1)->assertJsonPath('data.totals.finalized_gross_amount', 1000000)
            ->assertJsonPath('data.totals.refunded_amount', 467000)->assertJsonPath('data.currency', 'TOMAN');
        $this->getJson('/api/practitioner/v1/reports/daily?scope=date&date=2026-09-10')->assertOk()
            ->assertJsonPath('data.totals.appointments', 1)->assertJsonPath('data.totals.patient_no_show', 1)
            ->assertJsonPath('data.totals.pending_gross_amount', 1000000);
        $this->getJson('/api/practitioner/v1/reports/daily?scope=last_7_days')->assertOk()
            ->assertJsonCount(7, 'data.days')->assertJsonPath('data.from', '2026-09-08')
            ->assertJsonPath('data.to', '2026-09-14')->assertJsonPath('data.totals.appointments', 2);
    }

    public function test_daily_report_has_explicit_empty_days_and_validates_filters(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 18:00:00', 'Asia/Tehran'));
        $this->getJson('/api/practitioner/v1/reports/daily?scope=date&date=2026-09-01')->assertOk()
            ->assertJsonPath('data.totals.appointments', 0)->assertJsonPath('data.days.0.date', '2026-09-01')
            ->assertJsonPath('data.days.0.metrics.calls', 0)->assertJsonPath('data.days.0.metrics.call_results', []);
        $this->getJson('/api/practitioner/v1/reports/daily?scope=date')->assertUnprocessable()->assertJsonValidationErrors('date');
        $this->getJson('/api/practitioner/v1/reports/daily?scope=month')->assertUnprocessable()->assertJsonValidationErrors('scope');
        $this->getJson('/api/practitioner/v1/reports/daily?scope=date&date=1405/06/23')->assertUnprocessable()->assertJsonValidationErrors('date');
    }

    public function test_current_device_fcm_token_can_be_rotated_and_removed_without_touching_other_devices(): void
    {
        UserDevice::query()->create([
            'user_id' => $this->practitioner->user_id, 'access_token_id' => 1002, 'type' => 'ios',
            'fcm_token' => 'other-device-token', 'device_info' => ['device_identifier' => 'other-device'],
        ]);
        $url = '/api/practitioner/v1/devices/current';
        $this->patchJson($url, [
            'device_identifier' => 'profile-test-device', 'fcm_token' => 'fresh-fcm-token', 'device_version' => '1.4.0',
        ])->assertOk()->assertJsonPath('data.notifications_enabled', true)
            ->assertJsonPath('data.device_identifier', 'profile-test-device')->assertJsonPath('data.device_version', '1.4.0');
        $this->assertDatabaseHas('user_devices', ['access_token_id' => 1001, 'fcm_token' => 'fresh-fcm-token']);
        $this->assertDatabaseHas('user_devices', ['access_token_id' => 1002, 'fcm_token' => 'other-device-token']);

        $this->patchJson($url, ['device_identifier' => 'profile-test-device', 'fcm_token' => null])
            ->assertOk()->assertJsonPath('data.notifications_enabled', false);
        $this->patchJson($url, ['device_identifier' => 'unknown-device', 'fcm_token' => 'x'])->assertNotFound();
        $this->patchJson($url, ['device_identifier' => 'profile-test-device'])->assertUnprocessable()
            ->assertJsonValidationErrors('fcm_token');
    }

    public function test_high_traffic_read_endpoints_keep_bounded_query_counts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-14 18:00:00', 'Asia/Tehran'));
        foreach (range(1, 8) as $index) {
            $this->createAppointment('09126660'.str_pad((string) $index, 3, '0', STR_PAD_LEFT), '2026-09-14 10:00:00', 'PERF-'.$index);
        }

        DB::flushQueryLog(); DB::enableQueryLog();
        $this->getJson('/api/practitioner/v1/dashboard')->assertOk();
        $dashboardQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->getJson('/api/practitioner/v1/reports/daily')->assertOk();
        $reportQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(35, $dashboardQueries, 'Dashboard query count regressed.');
        $this->assertLessThanOrEqual(45, $reportQueries, 'Daily report query count regressed.');
    }

    public function test_practitioner_push_payload_contains_navigation_context(): void
    {
        $notification = new UserMessageNotification(
            'وضعیت تماس', 'تماس ثبت شد', 'جزئیات تماس را دریافت کنید.',
            ['type' => 'voip_call_result', 'appointment_id' => 42, 'call_id' => 'CALL-42'],
            '/appointments/42',
        );
        $payload = $notification->toArray($this->practitioner->user);
        $this->assertSame('voip_call_result', $payload['params']['type']);
        $this->assertSame(42, $payload['params']['appointment_id']);
        $this->assertSame('/appointments/42', $payload['link']);
    }

    private function createAppointment(string $mobile, string $dateVisit, string $trackingCode, int $status = 1): int
    {
        $patient = User::query()->create(['mobile' => $mobile, 'password' => 'password']);
        return DB::table('appointment_users')->insertGetId([
            'user_id' => $patient->id, 'doctor_id' => $this->practitioner->user_id,
            'kind' => 3, 'status' => $status, 'tracking_code' => $trackingCode,
            'date_visit' => $dateVisit, 'start_time' => substr($dateVisit, 11),
            'end_time' => Carbon::parse($dateVisit)->addMinutes(30)->format('H:i:s'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('mobile')->nullable(); $table->string('password');
            $table->rememberToken(); $table->timestamps();
        });
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id'); $table->unsignedBigInteger('access_token_id');
            $table->string('type'); $table->longText('fcm_token')->nullable(); $table->string('device_version')->nullable();
            $table->json('device_info')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('user_metas', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id'); $table->integer('meta_key');
            $table->text('meta_value')->nullable(); $table->timestamps();
        });
        Schema::create('consultation_settings', function (Blueprint $table): void {
            $table->id(); $table->boolean('booking_enabled')->default(false); $table->boolean('app_enabled')->default(false);
            $table->boolean('test_login_enabled')->default(false);
            $table->unsignedSmallInteger('duration_minutes')->default(20); $table->unsignedSmallInteger('buffer_minutes')->default(5);
            $table->unsignedSmallInteger('advance_hours')->default(2); $table->unsignedSmallInteger('booking_horizon_days')->default(30);
            $table->unsignedSmallInteger('cancellation_hours')->default(12); $table->unsignedSmallInteger('capacity_per_slot')->default(1);
            $table->unsignedBigInteger('default_fee')->default(0); $table->string('timezone')->default('Asia/Tehran');
            $table->string('voip_host')->nullable(); $table->unsignedSmallInteger('voip_port')->default(5061);
            $table->string('voip_transport')->default('tls');
            $table->text('voip_call_token')->nullable();
            $table->unsignedSmallInteger('ring_timeout_seconds')->default(30); $table->unsignedTinyInteger('max_attempts')->default(2);
            $table->unsignedTinyInteger('ignored_short_call_minutes')->default(6); $table->unsignedTinyInteger('connection_overhead_minutes')->default(6);
            $table->string('connection_method')->default('operator'); $table->string('voip_driver')->default('unconfigured');
            $table->json('app_landing_content')->nullable(); $table->timestamps();
        });
        Schema::create('consultation_practitioners', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique(); $table->string('display_name'); $table->string('kind');
            $table->string('specialty')->nullable(); $table->boolean('active'); $table->boolean('app_access');
            $table->boolean('tomorrow_schedule_sms_enabled')->default(false); $table->string('availability');
            $table->string('extension')->nullable(); $table->string('sip_username')->nullable(); $table->text('sip_secret')->nullable();
            $table->string('voip_host')->nullable();
            $table->unsignedBigInteger('hourly_rate')->nullable();
            $table->unsignedBigInteger('payout_hourly_rate')->nullable(); $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->json('weekly_schedule')->nullable(); $table->json('app_settings')->nullable(); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id(); $table->integer('setting_key')->unique(); $table->text('setting_value')->nullable();
        });
        Schema::create('services', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->integer('active')->default(1); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('specialities', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->integer('active')->default(1); $table->timestamps();
        });
        Schema::create('speciality_user', function (Blueprint $table): void {
            $table->id(); $table->foreignId('speciality_id'); $table->foreignId('user_id'); $table->timestamps();
        });
        Schema::create('places', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->integer('active')->default(1); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('place_user', function (Blueprint $table): void {
            $table->id(); $table->foreignId('place_id'); $table->foreignId('user_id'); $table->timestamps();
        });
        Schema::create('appointment_settings', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id'); $table->foreignId('service_id')->nullable(); $table->foreignId('place_id')->nullable();
            $table->integer('active')->default(1); $table->json('detail')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('appointment_setting_times', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_setting_id'); $table->integer('day_number');
            $table->time('start_at'); $table->time('end_at'); $table->date('special_date')->nullable(); $table->timestamps();
        });
        Schema::create('feedbacks', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_user_id')->nullable();
            $table->integer('answer'); $table->integer('question'); $table->timestamps();
        });
        Schema::create('appointment_users', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable(); $table->foreignId('doctor_id');
            $table->foreignId('service_id')->nullable(); $table->foreignId('place_id')->nullable();
            $table->integer('kind'); $table->integer('status'); $table->string('tracking_code')->nullable();
            $table->dateTime('date_visit')->nullable(); $table->time('start_time')->nullable(); $table->time('end_time')->nullable();
            $table->json('details')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->nullable();
            $table->unsignedBigInteger('transactionable_id'); $table->string('transactionable_type');
            $table->integer('status')->default(0); $table->integer('paid_by')->default(1);
            $table->bigInteger('cost')->default(0); $table->bigInteger('total_cost')->default(0);
            $table->json('detail')->nullable(); $table->timestamps();
        });
        Schema::create('user_wallets', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id'); $table->foreignId('transaction_id')->nullable();
            $table->bigInteger('amount_change'); $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after'); $table->string('type');
            $table->string('idempotency_key')->nullable()->unique(); $table->json('detail')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_call_logs', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->string('direction')->nullable();
            $table->string('final_result')->nullable(); $table->unsignedInteger('talk_duration_seconds')->default(0);
            $table->dateTime('call_entered_at')->nullable(); $table->dateTime('appointment_start_at')->nullable();
            $table->dateTime('appointment_end_at')->nullable(); $table->string('appointment_state')->nullable();
            $table->dateTime('answered_at')->nullable(); $table->dateTime('ended_at')->nullable();
            $table->string('disconnected_by')->nullable(); $table->string('connection_type')->nullable();
            $table->json('additional_data')->nullable(); $table->text('practitioner_note')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_callback_requests', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->string('request_id')->unique();
            $table->string('call_id')->nullable(); $table->foreignId('requested_by_id')->nullable();
            $table->string('requested_by_role'); $table->string('patient_phone'); $table->string('advisor_extension');
            $table->text('endpoint'); $table->string('status'); $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable(); $table->json('request_payload');
            $table->json('response_payload')->nullable(); $table->text('response_body')->nullable();
            $table->text('error_message')->nullable(); $table->dateTime('requested_at');
            $table->dateTime('completed_at')->nullable(); $table->dateTime('final_call_received_at')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_billing_records', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->foreignId('patient_id')->nullable();
            $table->foreignId('practitioner_id')->nullable(); $table->string('consultation_type')->nullable();
            $table->unsignedBigInteger('hourly_rate_snapshot')->default(0); $table->unsignedBigInteger('payout_hourly_rate_snapshot')->default(0);
            $table->string('refund_status')->nullable();
            $table->unsignedInteger('reserved_minutes')->default(0); $table->unsignedInteger('answered_talk_seconds')->default(0);
            $table->unsignedInteger('raw_answered_talk_seconds')->default(0); $table->unsignedInteger('ignored_talk_seconds')->default(0);
            $table->unsignedSmallInteger('connection_overhead_minutes_snapshot')->nullable(); $table->unsignedInteger('billable_talk_seconds')->default(0);
            $table->unsignedInteger('system_unused_minutes')->default(0); $table->unsignedInteger('approved_unused_minutes')->default(0);
            $table->unsignedBigInteger('total_paid_amount')->default(0); $table->unsignedBigInteger('suggested_refund_amount')->default(0);
            $table->unsignedBigInteger('refunded_amount')->default(0); $table->unsignedBigInteger('practitioner_earned_amount')->default(0);
            $table->unsignedBigInteger('platform_profit_amount')->default(0); $table->foreignId('wallet_transaction_id')->nullable();
            $table->foreignId('approved_by')->nullable(); $table->dateTime('approved_at')->nullable();
            $table->text('adjustment_reason')->nullable(); $table->unsignedBigInteger('used_amount')->default(0); $table->timestamps();
        });
        Schema::create('appointment_billing_audits', function (Blueprint $table): void {
            $table->id(); $table->foreignId('billing_record_id'); $table->string('action');
            $table->foreignId('actor_id')->nullable(); $table->unsignedInteger('system_unused_minutes')->default(0);
            $table->unsignedInteger('approved_unused_minutes')->default(0); $table->unsignedBigInteger('amount')->default(0);
            $table->text('reason')->nullable(); $table->json('snapshot'); $table->timestamps();
        });
        Schema::create('appointment_billing_adjustments', function (Blueprint $table): void {
            $table->id(); $table->foreignId('billing_record_id'); $table->bigInteger('amount_change')->default(0); $table->timestamps();
        });
        Schema::create('appointment_consultant_no_answers', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->string('call_id')->nullable();
            $table->dateTime('ring_started_at')->nullable(); $table->dateTime('no_answer_at')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_consultant_hangups', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->string('call_id')->nullable();
            $table->dateTime('hung_up_at')->nullable(); $table->string('hangup_via')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_consultation_cases', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id')->unique(); $table->string('state');
            $table->dateTime('completed_at')->nullable(); $table->foreignId('completed_by')->nullable();
            $table->dateTime('reopened_at')->nullable(); $table->foreignId('reopened_by')->nullable();
            $table->text('reopen_reason')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_consultation_reports', function (Blueprint $table): void {
            $table->id(); $table->foreignId('appointment_id'); $table->foreignId('case_id')->nullable();
            $table->foreignId('author_id')->nullable(); $table->string('outcome')->nullable();
            $table->string('title')->nullable(); $table->text('body')->nullable();
            $table->string('subject')->nullable(); $table->text('report_text')->nullable();
            $table->dateTime('follow_up_at')->nullable(); $table->timestamps();
        });
        Schema::create('appointment_consultation_case_events', function (Blueprint $table): void {
            $table->id(); $table->foreignId('case_id'); $table->foreignId('actor_id')->nullable();
            $table->string('action'); $table->text('reason')->nullable(); $table->json('snapshot')->nullable(); $table->timestamps();
        });
    }
}
