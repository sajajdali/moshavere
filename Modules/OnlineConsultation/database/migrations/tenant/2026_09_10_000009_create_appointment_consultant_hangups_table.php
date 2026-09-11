<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_consultant_hangups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointment_users')->cascadeOnDelete();
            $table->string('call_id', 100)->unique();
            $table->dateTimeTz('hung_up_at')->index();
            $table->string('hangup_via', 20)->index();
            $table->string('extension', 30)->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_payload');
            $table->timestamps();

            $table->index(['appointment_id', 'hung_up_at'], 'appt_hangups_appointment_hung_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_consultant_hangups');
    }
};
