<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointment_users')->nullOnDelete();
            $table->string('call_id', 100)->unique();
            $table->string('patient_phone', 40)->index();
            $table->string('destination', 80)->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('primary_extension', 30)->nullable();
            $table->string('connected_destination', 80)->nullable();
            $table->string('responded_by')->nullable();
            $table->string('direction', 10)->nullable()->index();
            $table->string('appointment_state', 40)->nullable();
            $table->string('connection_type', 20)->nullable();
            $table->string('final_result', 30)->index();
            $table->string('disconnected_by', 20)->nullable();
            $table->unsignedInteger('hangup_cause')->nullable();
            foreach (['appointment_start_at', 'appointment_end_at', 'call_entered_at', 'dial_started_at', 'answered_at', 'ended_at'] as $column) $table->dateTimeTz($column)->nullable()->index();
            $table->unsignedInteger('wait_duration_seconds')->default(0);
            $table->unsignedInteger('ring_duration_seconds')->default(0);
            $table->unsignedInteger('talk_duration_seconds')->default(0);
            $table->unsignedInteger('total_duration_seconds')->default(0);
            $table->json('attempts')->nullable();
            $table->json('additional_data')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
            $table->index(['appointment_id', 'created_at']);
            $table->index(['operator_id', 'created_at']);
            $table->index(['patient_phone', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('appointment_call_logs'); }
};
