<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_sms_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointment_users')->nullOnDelete();
            $table->foreignId('practitioner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50);
            $table->string('recipient', 30);
            $table->string('template');
            $table->dateTime('scheduled_at')->index();
            $table->dateTime('sent_at')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('provider_response')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->string('deduplication_key')->unique();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        foreach ([500 => 1, 501 => '', 502 => 180, 503 => 1, 504 => '', 505 => 60, 506 => 1, 507 => '', 508 => 15, 509 => 1, 510 => '', 511 => 15, 512 => 0, 513 => '', 514 => '23:00'] as $key => $value) {
            DB::table('settings')->insertOrIgnore(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_sms_deliveries');
    }
};
