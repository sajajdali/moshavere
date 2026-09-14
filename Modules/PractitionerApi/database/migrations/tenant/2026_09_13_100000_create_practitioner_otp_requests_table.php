<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practitioner_otp_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('mobile', 11)->unique();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedInteger('request_count')->default(1);
            $table->ipAddress('ip')->nullable();
            $table->string('device_identifier', 191)->nullable();
            $table->dateTime('expires_at')->index();
            $table->dateTime('next_request_at')->index();
            $table->dateTime('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practitioner_otp_requests');
    }
};
