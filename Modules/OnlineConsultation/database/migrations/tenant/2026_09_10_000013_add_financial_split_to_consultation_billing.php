<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('ignored_short_call_minutes')->default(6)->after('max_attempts');
        });

        Schema::table('consultation_practitioners', function (Blueprint $table) {
            $table->unsignedBigInteger('payout_hourly_rate')->nullable()->after('hourly_rate');
        });

        Schema::table('appointment_billing_records', function (Blueprint $table) {
            $table->unsignedBigInteger('payout_hourly_rate_snapshot')->default(0)->after('hourly_rate_snapshot');
            $table->unsignedInteger('raw_answered_talk_seconds')->default(0)->after('reserved_minutes');
            $table->unsignedInteger('ignored_talk_seconds')->default(0)->after('raw_answered_talk_seconds');
            $table->unsignedBigInteger('practitioner_earned_amount')->default(0)->after('refunded_amount');
            $table->bigInteger('platform_profit_amount')->default(0)->after('practitioner_earned_amount');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_billing_records', function (Blueprint $table) {
            $table->dropColumn([
                'payout_hourly_rate_snapshot', 'raw_answered_talk_seconds', 'ignored_talk_seconds',
                'practitioner_earned_amount', 'platform_profit_amount',
            ]);
        });
        Schema::table('consultation_practitioners', fn (Blueprint $table) => $table->dropColumn('payout_hourly_rate'));
        Schema::table('consultation_settings', fn (Blueprint $table) => $table->dropColumn('ignored_short_call_minutes'));
    }
};
