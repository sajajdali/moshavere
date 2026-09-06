<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_settings', function (Blueprint $t) {
            $t->id();
            $t->boolean('booking_enabled')->default(false);
            $t->boolean('app_enabled')->default(false);
            $t->unsignedSmallInteger('duration_minutes')->default(20);
            $t->unsignedSmallInteger('buffer_minutes')->default(5);
            $t->unsignedSmallInteger('advance_hours')->default(2);
            $t->unsignedSmallInteger('booking_horizon_days')->default(30);
            $t->unsignedSmallInteger('cancellation_hours')->default(12);
            $t->unsignedSmallInteger('capacity_per_slot')->default(1);
            $t->unsignedBigInteger('default_fee')->default(0);
            $t->string('timezone')->default('Asia/Tehran');
            $t->string('connection_method')->default('operator');
            $t->string('voip_driver')->default('unconfigured');
            $t->string('voip_host')->nullable();
            $t->unsignedSmallInteger('voip_port')->default(5061);
            $t->string('voip_transport')->default('tls');
            $t->string('voip_username')->nullable();
            $t->text('voip_secret')->nullable();
            $t->string('outbound_caller_id', 30)->nullable();
            $t->string('queue_number', 20)->nullable();
            $t->unsignedSmallInteger('ring_timeout_seconds')->default(30);
            $t->unsignedTinyInteger('max_attempts')->default(2);
            $t->boolean('allow_transfer')->default(false);
            $t->boolean('recording_requested')->default(false);
            $t->boolean('consent_required')->default(true);
            $t->text('patient_instructions')->nullable();
            $t->timestamps();
        });
        Schema::create('consultation_practitioners', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $t->string('display_name');
            $t->string('kind', 20);
            $t->string('specialty')->nullable();
            $t->boolean('active')->default(true);
            $t->boolean('app_access')->default(false);
            $t->string('availability', 20)->default('offline');
            $t->string('extension', 20)->nullable()->unique();
            $t->string('voip_host')->nullable();
            $t->string('sip_username')->nullable();
            $t->text('sip_secret')->nullable();
            $t->unsignedBigInteger('fee')->nullable();
            $t->unsignedSmallInteger('duration_minutes')->nullable();
            $t->json('weekly_schedule')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('consultations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('practitioner_id')->constrained('consultation_practitioners')->restrictOnDelete();
            $t->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 30)->default('scheduled')->index();
            $t->dateTime('scheduled_at')->index();
            $t->dateTime('ended_at')->nullable();
            $t->unsignedSmallInteger('duration_minutes');
            $t->unsignedBigInteger('fee')->default(0);
            $t->text('outcome')->nullable();
            $t->timestamps();
        });
        Schema::create('consultation_calls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('consultation_id')->constrained('consultations')->restrictOnDelete();
            $t->string('provider_call_id')->nullable()->unique();
            $t->string('status', 30)->default('pending')->index();
            $t->dateTime('started_at')->nullable();
            $t->dateTime('answered_at')->nullable();
            $t->dateTime('ended_at')->nullable();
            $t->unsignedInteger('duration_seconds')->default(0);
            $t->timestamps();
        });
        DB::table('permissions')->insertOrIgnore([
            'name' => 'ONLINE_CONSULTATION_MANAGE', 'guard_name' => 'web',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_calls');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('consultation_practitioners');
        Schema::dropIfExists('consultation_settings');
    }
};
