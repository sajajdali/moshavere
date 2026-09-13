<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_alternate_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointment_users')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->string('phone', 11)->unique('alt_phone_phone_unique');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['appointment_id', 'created_at'], 'alt_phone_appt_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_alternate_phones');
    }
};
