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
        Schema::create('appointment_online_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentUser\app\Models\AppointmentOnline::class)->nullable()->constrained('appointment_online')->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\User\Entities\User::class,'answer_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->smallInteger('type')->default(1)->comment('1 = question | 2 = answer');
            $table->boolean('seen')->default(0);
            $table->text('body')->nullable();
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
        Schema::dropIfExists('appointment_online_messages');
    }
};
