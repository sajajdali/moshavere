<?php

namespace Modules\Api\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Http\Controllers\Voip\AppointmentStatusController;
use Modules\Api\Http\Controllers\Voip\CallLogController;
use Modules\Api\Http\Controllers\Voip\ConsultantHangupController;
use Modules\Api\Http\Controllers\Voip\ConsultantNoAnswerController;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Tests\TestCase;

class AppointmentStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        (require base_path('Modules/User/Database/Migrations/tenant/2023_08_02_073105_create_users_table.php'))->up();
        (require base_path('Modules/User/Database/Migrations/tenant/2023_08_02_073110_create_user_metas_table.php'))->up();

        Schema::create('appointment_users', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('doctor_id')->nullable();
            $table->string('tracking_code')->nullable();
            $table->unsignedTinyInteger('status');
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedTinyInteger('kind')->default(1);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->dateTime('date_visit')->nullable();
            $table->json('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('consultation_practitioners', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('extension')->nullable();
            $table->timestamps();
        });
        Schema::create('appointment_call_logs', function ($table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable();
            $table->string('call_id')->unique();
            $table->string('patient_phone');
            $table->foreignId('operator_id')->nullable();
            $table->string('destination')->nullable();
            $table->string('primary_extension')->nullable();
            $table->string('connected_destination')->nullable();
            $table->string('responded_by')->nullable();
            $table->string('direction')->nullable();
            $table->string('appointment_state');
            $table->string('connection_type');
            $table->string('final_result');
            $table->string('disconnected_by')->nullable();
            $table->unsignedInteger('hangup_cause')->nullable();
            foreach (['appointment_start_at', 'appointment_end_at', 'call_entered_at', 'dial_started_at', 'answered_at', 'ended_at'] as $column) $table->dateTime($column)->nullable();
            foreach (['wait_duration_seconds', 'ring_duration_seconds', 'talk_duration_seconds', 'total_duration_seconds'] as $column) $table->unsignedInteger($column)->default(0);
            $table->json('attempts')->nullable();
            $table->json('additional_data')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
        Schema::create('appointment_consultant_hangups', function ($table) {
            $table->id();
            $table->foreignId('appointment_id');
            $table->string('call_id')->unique();
            $table->dateTime('hung_up_at');
            $table->string('hangup_via');
            $table->string('extension')->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
        Schema::create('appointment_consultant_no_answers', function ($table) {
            $table->id();
            $table->foreignId('appointment_id');
            $table->string('call_id')->unique();
            $table->dateTime('no_answer_at');
            $table->dateTime('ring_started_at')->nullable();
            $table->unsignedInteger('ring_duration_seconds')->default(0);
            $table->string('extension')->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
        Schema::create('appointment_consultation_cases', function ($table) {
            $table->id();
            $table->foreignId('appointment_id')->unique();
            $table->string('state')->default('OPEN');
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable();
            $table->dateTime('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('appointment_alternate_phones', function ($table) {
            $table->id();
            $table->foreignId('appointment_id');
            $table->foreignId('patient_id');
            $table->string('phone', 11)->unique();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });
        Schema::create('consultation_settings', function ($table) {
            $table->id();
            $table->unsignedTinyInteger('ignored_short_call_minutes')->default(6);
            $table->timestamps();
        });
        DB::table('consultation_settings')->insert([
            'id' => 1,
            'ignored_short_call_minutes' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_appointment_remains_connectable_until_its_end_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-06 22:12:00', 'Asia/Tehran'));

        DB::table('users')->insert([
            ['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'mobile' => '09120000000', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('appointment_users')->insert([
            'user_id' => 1,
            'doctor_id' => 2,
            'tracking_code' => '82268361',
            'status' => 1,
            'date_visit' => '2026-09-06 22:00:00',
            'start_time' => '22:00:00',
            'end_time' => '23:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('consultation_practitioners')->insert([
            'user_id' => 2,
            'extension' => '102',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '09122978167'])
        );
        $payload = $response->getData(true);

        $this->assertSame(0, $payload['error_code']);
        $this->assertTrue($payload['has_appointment']);
        $this->assertTrue($payload['has_appointment_today']);
        $this->assertTrue($payload['is_time_for_appointment']);
        $this->assertTrue($payload['can_connect']);
        $this->assertSame(1, $payload['appointment_id']);
        $this->assertSame('102', $payload['doctor_extension']);
        $this->assertSame('09120000000', $payload['doctor_mobile']);
        $this->assertSame(1, $payload['appointments'][0]['appointment_id']);
        $this->assertSame('09120000000', $payload['appointments'][0]['doctor_mobile']);
        $this->assertSame('23:00:00', $payload['appointments'][0]['end_time']);
        $this->assertSame(6, $payload['ignored_short_call_minutes']);
        $this->assertSame(360, $payload['ignored_short_call_seconds']);
        $this->assertSame(6, $payload['appointments'][0]['ignored_short_call_minutes']);
        $this->assertSame(360, $payload['appointments'][0]['ignored_short_call_seconds']);
    }

    public function test_registered_landline_resolves_all_appointments_of_its_patient(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-06 22:12:00', 'Asia/Tehran'));
        DB::table('users')->insert([
            ['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'mobile' => '09120000000', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('appointment_users')->insert([
            'id' => 45, 'user_id' => 1, 'doctor_id' => 2, 'tracking_code' => 'LANDLINE-1', 'status' => 1,
            'date_visit' => '2026-09-06 22:00:00', 'start_time' => '22:00:00', 'end_time' => '23:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_users')->insert([
            'id' => 46, 'user_id' => 1, 'doctor_id' => 2, 'tracking_code' => 'LANDLINE-FUTURE', 'status' => 1,
            'date_visit' => '2026-09-07 18:00:00', 'start_time' => '18:00:00', 'end_time' => '18:30:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('appointment_alternate_phones')->insert([
            'appointment_id' => 45, 'patient_id' => 1, 'phone' => '02112345678',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('consultation_practitioners')->insert([
            'user_id' => 2, 'extension' => '102', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $payload = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '02112345678'])
        )->getData(true);

        $this->assertTrue($payload['has_appointment']);
        $this->assertTrue($payload['can_connect']);
        $this->assertSame(45, $payload['appointment_id']);
        $this->assertSame('102', $payload['doctor_extension']);
        $this->assertCount(2, $payload['appointments']);

        $localNumberPayload = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '12345678'])
        )->getData(true);
        $this->assertTrue($localNumberPayload['has_appointment']);
        $this->assertSame(45, $localNumberPayload['appointment_id']);
    }

    public function test_doctor_mobile_is_hidden_before_the_appointment_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-06 20:00:00', 'Asia/Tehran'));
        DB::table('users')->insert([
            ['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'mobile' => '09120000000', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('appointment_users')->insert([
            'user_id' => 1, 'doctor_id' => 2, 'tracking_code' => 'FUTURE-1', 'status' => 1,
            'date_visit' => '2026-09-06 22:00:00', 'start_time' => '22:00:00', 'end_time' => '23:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('consultation_practitioners')->insert([
            'user_id' => 2, 'extension' => '102', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $payload = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '09122978167'])
        )->getData(true);

        $this->assertFalse($payload['can_connect']);
        $this->assertNull($payload['doctor_mobile']);
        $this->assertArrayNotHasKey('doctor_mobile', $payload['appointments'][0]);
    }

    public function test_completed_consultation_is_explicitly_returned_and_cannot_connect(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-06 22:12:00', 'Asia/Tehran'));
        DB::table('users')->insert([
            ['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'mobile' => '09120000000', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('appointment_users')->insert([
            'id' => 125, 'user_id' => 1, 'doctor_id' => 2, 'tracking_code' => 'DONE-1', 'status' => 1,
            'date_visit' => '2026-09-06 22:00:00', 'start_time' => '22:00:00', 'end_time' => '23:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('consultation_practitioners')->insert(['user_id' => 2, 'extension' => '102', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_consultation_cases')->insert([
            'appointment_id' => 125, 'state' => 'COMPLETED', 'completed_at' => now(), 'completed_by' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $payload = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '09122978167'])
        )->getData(true);

        $this->assertSame(1003, $payload['error_code']);
        $this->assertTrue($payload['consultation_completed']);
        $this->assertSame('COMPLETED', $payload['consultation_status']);
        $this->assertFalse($payload['can_connect']);
        $this->assertTrue($payload['is_time_for_appointment']);
        $this->assertNull($payload['doctor_mobile']);
        $this->assertSame('مشاوره تمام شده است و اتصال مجدد مجاز نیست.', $payload['message']);

        DB::table('appointment_consultation_cases')->where('appointment_id', 125)->update(['state' => 'PATIENT_NO_SHOW']);
        $noShow = (new AppointmentStatusController())->show(
            Request::create('/api/v1/VoIP/appointment_status', 'GET', ['phone' => '09122978167'])
        )->getData(true);
        $this->assertSame('PATIENT_NO_SHOW', $noShow['consultation_status']);
        $this->assertTrue($noShow['consultation_completed']);
        $this->assertFalse($noShow['can_connect']);
        $this->assertNull($noShow['doctor_mobile']);

    }

    public function test_call_report_echoes_appointment_id_and_retry_updates_the_same_call(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'ABC12345', 'status' => 1, 'date_visit' => now()->addHour(), 'created_at' => now(), 'updated_at' => now()]);
        $payload = [
            'call_id' => '1788759001.127', 'appointment_id' => 125, 'patient_phone' => '09122978167',
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'ANSWERED',
            'connection_type' => 'DIRECT', 'primary_extension' => '102', 'talk_duration_seconds' => 120,
            'answered_at' => '2026-09-10T14:29:22+03:30', 'ended_at' => '2026-09-10T14:31:22+03:30',
            'disconnected_by' => 'DOCTOR', 'survey_score' => 4,
        ];

        $controller = new CallLogController();
        $first = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', $payload))->getData(true);
        $second = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', array_merge($payload, ['talk_duration_seconds' => 180])))->getData(true);

        $this->assertSame(125, $first['appointment_id']);
        $this->assertSame(125, $second['appointment_id']);
        $this->assertSame(1, DB::table('appointment_call_logs')->count());
        $this->assertSame(180, DB::table('appointment_call_logs')->value('talk_duration_seconds'));
        $this->assertSame(4, AppointmentCallLog::first()->surveyScore());
        $this->assertSame(4, $second['survey_score']);
        $details = json_decode(DB::table('appointment_users')->value('details'), true);
        $this->assertSame(4, data_get($details, 'SURVEY.SURVEY'));
    }

    public function test_patient_hangup_is_stored_with_full_timeline_without_consultant_hangup_event(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'PATIENT-HANGUP', 'status' => 1, 'date_visit' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $payload = [
            'call_id' => '1788759001.128', 'appointment_id' => 125, 'patient_phone' => '09122978167',
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'ANSWERED',
            'connection_type' => 'DIRECT', 'primary_extension' => '102', 'connected_destination' => '102',
            'answered_at' => '2026-09-10T14:29:22+03:30', 'ended_at' => '2026-09-10T14:29:50+03:30',
            'talk_duration_seconds' => 28, 'total_duration_seconds' => 35, 'disconnected_by' => 'PATIENT',
            'attempts' => [[
                'order' => 1, 'type' => 'PRIMARY', 'destination' => '102', 'dial_status' => 'ANSWER',
                'answered_at' => '2026-09-10T14:29:22+03:30', 'ended_at' => '2026-09-10T14:29:50+03:30',
                'talk_duration_seconds' => 28,
            ]],
        ];

        $controller = new CallLogController();
        $first = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', $payload))->getData(true);
        $second = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', $payload))->getData(true);
        $call = AppointmentCallLog::with('consultantHangup')->firstOrFail();

        $this->assertTrue($first['status']);
        $this->assertSame('ANSWERED', $first['final_result']);
        $this->assertSame('PATIENT', $first['disconnected_by']);
        $this->assertSame(28, $first['talk_duration_seconds']);
        $this->assertSame('DIRECT', $first['connection_type']);
        $this->assertSame(1, $first['connection_attempts_count']);
        $this->assertFalse($first['consultant_hangup_recorded']);
        $this->assertSame('PATIENT', $second['disconnected_by']);
        $this->assertSame(1, DB::table('appointment_call_logs')->count());
        $this->assertSame(0, DB::table('appointment_consultant_hangups')->count());
        $this->assertTrue($call->wasDisconnectedByPatient());
        $this->assertSame(28, $call->talk_duration_seconds);
        $this->assertCount(1, $call->attempts);
        $this->assertNotNull($call->answered_at);
        $this->assertNotNull($call->ended_at);
    }

    public function test_consultant_hangup_is_recorded_and_retry_updates_the_same_event(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'ABC12345', 'status' => 1, 'date_visit' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $callPayload = [
            'call_id' => '1788759001.127', 'appointment_id' => 125, 'patient_phone' => '09122978167',
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'ANSWERED',
            'connection_type' => 'DIRECT', 'disconnected_by' => 'PATIENT',
        ];
        (new CallLogController())->store(Request::create('/api/v1/VoIP/call_log', 'POST', $callPayload));
        $payload = [
            'call_id' => '1788759001.127',
            'appointment_id' => 125,
            'hung_up_at' => '2026-09-10T14:31:22+03:30',
            'hangup_via' => 'PHONE',
            'extension' => '102',
            'channel' => 'PJSIP/102-000001a2',
        ];

        $controller = new ConsultantHangupController();
        $first = $controller->store(Request::create('/api/v1/VoIP/consultant_hangup', 'POST', $payload))->getData(true);
        $second = $controller->store(Request::create('/api/v1/VoIP/consultant_hangup', 'POST', array_merge($payload, ['hangup_via' => 'SOFTPHONE'])))->getData(true);

        $this->assertTrue($first['status']);
        $this->assertSame(125, $first['appointment_id']);
        $this->assertSame('SOFTPHONE', $second['hangup_via']);
        $this->assertSame(1, DB::table('appointment_consultant_hangups')->count());
        $this->assertSame('SOFTPHONE', DB::table('appointment_consultant_hangups')->value('hangup_via'));
        $this->assertSame('DOCTOR', DB::table('appointment_call_logs')->value('disconnected_by'));

        $callResponse = (new CallLogController())->store(Request::create('/api/v1/VoIP/call_log', 'POST', $callPayload))->getData(true);
        $this->assertSame('DOCTOR', $callResponse['disconnected_by']);
        $this->assertSame('DOCTOR', DB::table('appointment_call_logs')->value('disconnected_by'));
    }

    public function test_consultant_hangup_rejects_unknown_device_type(): void
    {
        $response = (new ConsultantHangupController())->store(Request::create('/api/v1/VoIP/consultant_hangup', 'POST', [
            'call_id' => '1788759001.127',
            'appointment_id' => 999,
            'hung_up_at' => '2026-09-10T14:31:22+03:30',
            'hangup_via' => 'WEB',
        ]));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(2002, $response->getData(true)['error_code']);
    }

    public function test_consultant_no_answer_is_recorded_idempotently(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'ABC12345', 'status' => 1, 'date_visit' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $payload = [
            'call_id' => '1788759001.128',
            'appointment_id' => 125,
            'ring_started_at' => '2026-09-10T14:30:42+03:30',
            'no_answer_at' => '2026-09-10T14:31:22+03:30',
            'ring_duration_seconds' => 40,
            'extension' => '102',
            'channel' => 'PJSIP/102-000001a3',
        ];

        $controller = new ConsultantNoAnswerController();
        $first = $controller->store(Request::create('/api/v1/VoIP/consultant_no_answer', 'POST', $payload))->getData(true);
        $second = $controller->store(Request::create('/api/v1/VoIP/consultant_no_answer', 'POST', array_merge($payload, ['ring_duration_seconds' => 45])))->getData(true);

        $this->assertTrue($first['status']);
        $this->assertSame(125, $first['appointment_id']);
        $this->assertSame(45, $second['ring_duration_seconds']);
        $this->assertSame(1, DB::table('appointment_consultant_no_answers')->count());
        $this->assertSame(45, DB::table('appointment_consultant_no_answers')->value('ring_duration_seconds'));
    }

    public function test_consultant_no_answer_event_is_ignored_for_an_early_call(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'ABC12345', 'status' => 1, 'date_visit' => '2026-09-10 15:00:00', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_call_logs')->insert([
            'appointment_id' => 125,
            'call_id' => '1788759001.early',
            'patient_phone' => '09122978167',
            'appointment_state' => 'IN_APPOINTMENT_TIME', // Deliberately inconsistent PBX state.
            'connection_type' => 'NONE',
            'final_result' => 'CALLER_ABANDONED',
            'appointment_start_at' => '2026-09-10 15:00:00',
            'appointment_end_at' => '2026-09-10 15:30:00',
            'call_entered_at' => '2026-09-10 14:46:51',
            'raw_payload' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new ConsultantNoAnswerController())->store(Request::create('/api/v1/VoIP/consultant_no_answer', 'POST', [
            'call_id' => '1788759001.early',
            'appointment_id' => 125,
            'ring_started_at' => '2026-09-10T14:46:30+03:30',
            'no_answer_at' => '2026-09-10T14:46:51+03:30',
            'ring_duration_seconds' => 21,
        ]))->getData(true);

        $this->assertTrue($response['status']);
        $this->assertTrue($response['ignored_as_early_call']);
        $this->assertSame(0, DB::table('appointment_consultant_no_answers')->count());
    }

    public function test_consultation_amount_uses_fixed_half_up_integer_rounding(): void
    {
        $service = new AppointmentBillingService();
        $this->assertSame(667000, $service->amountForMinutes(1000000, 40));
        $this->assertSame(333000, $service->amountForMinutes(1000000, 20));
    }
}
