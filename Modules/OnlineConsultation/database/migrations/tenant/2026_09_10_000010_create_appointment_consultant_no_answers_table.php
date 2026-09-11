<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_consultant_no_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointment_users')->cascadeOnDelete();
            $table->string('call_id', 100)->unique();
            $table->dateTimeTz('no_answer_at')->index();
            $table->dateTimeTz('ring_started_at')->nullable();
            $table->unsignedInteger('ring_duration_seconds')->default(0);
            $table->string('extension', 30)->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_payload');
            $table->timestamps();

            $table->index(['appointment_id', 'no_answer_at'], 'appt_no_answers_appointment_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_consultant_no_answers');
    }
};
