<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_callback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointment_users')->restrictOnDelete();
            $table->string('request_id', 120)->unique();
            $table->string('call_id', 100)->nullable()->index();
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_by_role', 30)->default('consultant');
            $table->string('patient_phone', 40)->index();
            $table->string('advisor_extension', 30);
            $table->text('endpoint');
            $table->string('status', 30)->default('PENDING')->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->dateTimeTz('requested_at')->index();
            $table->dateTimeTz('completed_at')->nullable();
            $table->dateTimeTz('final_call_received_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'requested_at'], 'appointment_callback_requested_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_callback_requests');
    }
};
