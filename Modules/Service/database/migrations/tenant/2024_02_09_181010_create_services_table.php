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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignIdFor(\Modules\Service\app\Models\Service::class  ,'parent_id')->nullable()->constrained('services')->cascadeOnDelete();
            $table->string('icon')->nullable();
            $table->integer('priority')->default(1);
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('show_type')->default(1)->comment('1=> show in the main page | 2 => not show in the main page');
            $table->string('api_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
