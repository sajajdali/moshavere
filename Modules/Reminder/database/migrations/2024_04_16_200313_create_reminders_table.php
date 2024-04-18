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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('reminderable');
            $table->tinyInteger('status')->default(1)->comment("1 = sms | 2 = notification | 3 = call ");
            $table->string('body');
            $table->json('parameters')->nullable();
            $table->json('doctors')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->json('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
