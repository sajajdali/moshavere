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
        Schema::create('operator_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oprator_id')->constrained('users','id');
            $table->tinyInteger('day_number')->default(0)->comment('0 = Saturday | 6 = friday');
            $table->time('start_at');
            $table->time('end_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_times');
    }
};
