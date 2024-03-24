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
        Schema::create('appointment_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSetting::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Service\app\Models\Service::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Place\app\Models\Place::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class,'doctor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class , 'agent_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class , 'operator_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('tracking_code' , 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('type')->default(1)->comment('The main appointment or between patients');
            $table->tinyInteger('kind')->default(1)->comment('in person - online - by phone of any kind');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('date_visit')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->ipAddress('user_ip')->nullable();
            $table->json('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_users');
    }
};
