<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;
use Modules\AppointmentUser\app\Models\AppointmentUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_user_segment_item', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AppointmentUser::class)
                ->constrained()
                ->cascadeOnDelete()
                ->index('fk_appointment_user_id');

            $table->foreignIdFor(AppointmentSegmentItem::class)
                ->constrained()
                ->cascadeOnDelete()
                ->index('fk_appointment_segment_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_user_segment_item');
    }
};
