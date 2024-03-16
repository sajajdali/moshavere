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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Service\app\Models\Service::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Modules\Place\app\Models\Place::class)->nullable()->constrained()->cascadeOnDelete();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence');
    }
};
