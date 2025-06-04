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
        Schema::create('place_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained();
            $table->foreignId('place_id')->nullable()->constrained();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_service');
    }
};
