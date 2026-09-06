<?php

namespace Modules\Api\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Api\Http\Controllers\Voip\AppointmentStatusController;
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
        $this->assertSame('23:00:00', $payload['appointments'][0]['end_time']);
    }
}
