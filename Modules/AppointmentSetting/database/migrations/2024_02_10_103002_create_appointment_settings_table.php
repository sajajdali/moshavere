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
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Service\app\Models\Service::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Place\app\Models\Place::class)->nullable()->constrained()->cascadeOnDelete();
            $table->integer('time_for_visit')->default(0);
            $table->integer('min_day_active')->default(0);
            $table->integer('max_day_active')->nullable();
            $table->integer('cancellation_by_user')->nullable()->default(0)->comment('null = for deactive | 0-1000 = number of the before canceled');
            $table->date('last_day_active')->nullable();
            $table->dateTime('first_day_active')->nullable();
            $table->tinyInteger('active_payment')->default(0)->comment('1 = active | 0 = deactivate');
            $table->tinyInteger('interference')->default(0);
            $table->tinyInteger('active')->default(1);
            $table->json('detail')->nullable();
            $table->timestamp('updated_log_at')->nullable();
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
