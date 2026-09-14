<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practitioner_otp_requests', function (Blueprint $table): void {
            $table->dateTime('locked_until')->nullable()->index()->after('next_request_at');
        });
    }

    public function down(): void
    {
        Schema::table('practitioner_otp_requests', function (Blueprint $table): void {
            $table->dropColumn('locked_until');
        });
    }
};
