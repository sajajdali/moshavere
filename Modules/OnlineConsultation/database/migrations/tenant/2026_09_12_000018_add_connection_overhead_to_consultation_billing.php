<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('connection_overhead_minutes')->default(6)->after('ignored_short_call_minutes');
        });

        Schema::table('appointment_billing_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('connection_overhead_minutes_snapshot')->nullable()->after('ignored_talk_seconds');
            $table->unsignedInteger('billable_talk_seconds')->default(0)->after('connection_overhead_minutes_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_billing_records', fn (Blueprint $table) => $table->dropColumn([
            'connection_overhead_minutes_snapshot', 'billable_talk_seconds',
        ]));
        Schema::table('consultation_settings', fn (Blueprint $table) => $table->dropColumn('connection_overhead_minutes'));
    }
};
