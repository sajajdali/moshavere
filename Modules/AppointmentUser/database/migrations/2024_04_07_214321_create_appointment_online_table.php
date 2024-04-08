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
        Schema::create('appointment_online', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSetting::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\AppointmentUser\app\Models\AppointmentUser::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class,'doctor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('tracking_code' , 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('message_status')->default(0)->comment("0 = new | 1 = question user | 2 = close | 3 = reject | 9 = answered");
            $table->timestamp('date_visit')->nullable();
            $table->json('details')->nullable();
            $table->smallInteger('new_messages')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_online');
    }
};
