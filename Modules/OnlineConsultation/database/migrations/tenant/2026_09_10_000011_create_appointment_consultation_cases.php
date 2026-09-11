<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_consultation_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointment_users')->cascadeOnDelete();
            $table->string('state', 20)->default('OPEN')->index();
            $table->dateTimeTz('completed_at')->nullable()->index();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTimeTz('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('appointment_consultation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('appointment_consultation_cases')->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained('appointment_users')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('outcome', 40)->index();
            $table->string('subject', 200);
            $table->text('report_text');
            $table->dateTimeTz('follow_up_at')->nullable()->index();
            $table->timestamps();

            $table->index(['appointment_id', 'created_at'], 'appt_reports_appointment_created_idx');
        });

        Schema::create('appointment_consultation_case_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('appointment_consultation_cases')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30)->index();
            $table->text('reason')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_consultation_case_events');
        Schema::dropIfExists('appointment_consultation_reports');
        Schema::dropIfExists('appointment_consultation_cases');
    }
};
