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
        Schema::create('appointment_setting_times', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSetting::class)->nullable()->constrained('appointment_settings')->cascadeOnDelete();
            $table->tinyInteger('day_number')->default(0)->comment('0 = Saturday | 6 = friday');
            $table->time('start_at');
            $table->time('end_at');
            $table->date('special_date')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_setting_times');
    }
};
