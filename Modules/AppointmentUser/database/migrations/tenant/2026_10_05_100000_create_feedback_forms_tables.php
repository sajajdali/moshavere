<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // a form can be bound to a doctor, service and place (null = applies to all)
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('feedback_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 20); // text, textarea, select, radio, checkbox, rating
            $table->json('options')->nullable();
            $table->boolean('required')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('feedback_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_user_id')->constrained('appointment_users')->cascadeOnDelete();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->cascadeOnDelete();
            $table->foreignId('feedback_form_question_id')->constrained('feedback_form_questions')->cascadeOnDelete();
            $table->text('answer')->nullable(); // checkbox answers are stored as json array
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_answers');
        Schema::dropIfExists('feedback_form_questions');
        Schema::dropIfExists('feedback_forms');
    }
};
