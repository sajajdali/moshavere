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
        Schema::create('appointment_segment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\AppointmentSetting\app\Models\AppointmentSegment::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->tinyInteger('display_on_site')->default(1);
            $table->integer('price')->nullable();
            $table->integer('priority')->default(1);
            $table->integer('time')->nullable();
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
        Schema::dropIfExists('appointment_segment_items');
    }
};
