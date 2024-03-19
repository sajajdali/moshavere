<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_segment_setting', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSetting::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSegment::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_segment_setting');
    }
};
