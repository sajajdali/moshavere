<?php

namespace Modules\Api\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Http\Controllers\Voip\AppointmentStatusController;
use Modules\Api\Http\Controllers\Voip\CallLogController;
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

    public function test_call_report_echoes_appointment_id_and_retry_updates_the_same_call(): void
    {
        DB::table('users')->insert(['id' => 1, 'mobile' => '09122978167', 'password' => 'test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('appointment_users')->insert(['id' => 125, 'user_id' => 1, 'tracking_code' => 'ABC12345', 'status' => 1, 'date_visit' => now()->addHour(), 'created_at' => now(), 'updated_at' => now()]);
        $payload = [
            'call_id' => '1788759001.127', 'appointment_id' => 125, 'patient_phone' => '09122978167',
            'appointment_state' => 'IN_APPOINTMENT_TIME', 'final_result' => 'ANSWERED',
            'connection_type' => 'DIRECT', 'primary_extension' => '102', 'talk_duration_seconds' => 120,
        ];

        $controller = new CallLogController();
        $first = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', $payload))->getData(true);
        $second = $controller->store(Request::create('/api/v1/VoIP/call_log', 'POST', array_merge($payload, ['talk_duration_seconds' => 180])))->getData(true);

        $this->assertSame(125, $first['appointment_id']);
        $this->assertSame(125, $second['appointment_id']);
        $this->assertSame(1, DB::table('appointment_call_logs')->count());
        $this->assertSame(180, DB::table('appointment_call_logs')->value('talk_duration_seconds'));
    }

    public function test_consultation_amount_uses_fixed_half_up_integer_rounding(): void
    {
        $service = new AppointmentBillingService();
        $this->assertSame(667000, $service->amountForMinutes(1000000, 40));
        $this->assertSame(333000, $service->amountForMinutes(1000000, 20));
    }
}
