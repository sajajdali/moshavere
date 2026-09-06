<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voip_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path');
            $table->string('phone', 40)->nullable()->index();
            $table->string('client_ip', 45)->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedInteger('error_code')->nullable()->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voip_request_logs');
    }
};
