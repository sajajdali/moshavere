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
        Schema::create('appointment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->constrained()->cascadeOnDelete()->nullable();
            $table->foreignIdFor(\Modules\Service\app\Models\Service::class)->constrained()->cascadeOnDelete()->nullable();
            $table->foreignIdFor(\Modules\Place\app\Models\Place::class)->constrained()->cascadeOnDelete()->nullable();
            $table->integer('time_for_visit')->default(0);
            $table->integer('min_day_active')->default(0);
            $table->integer('max_day_active')->nullable();
            $table->tinyInteger('cancellation_by_user')->default(1);
            $table->tinyInteger('last_day_active')->nullable();
            $table->tinyInteger('active_payment')->default(0)->comment('1 = active | 0 = deactivate');
            $table->tinyInteger('interference')->default(0);
            $table->tinyInteger('active')->default(1);
            $table->json('detail')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_settings');
    }
};
