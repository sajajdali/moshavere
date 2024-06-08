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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Modules\User\Entities\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->references('id')->on('users');
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->references('id')->on('comments');
            $table->integer('star')->nullable();
            $table->integer('like')->default(0);
            $table->integer('status')->default(0);
            $table->text('reply')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
