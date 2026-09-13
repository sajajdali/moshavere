<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_practitioners', function (Blueprint $table) {
            $table->boolean('tomorrow_schedule_sms_enabled')->default(false)->after('app_access');
        });

        DB::table('settings')->updateOrInsert(
            ['setting_key' => 514],
            ['setting_value' => '23:00'],
        );
    }

    public function down(): void
    {
        Schema::table('consultation_practitioners', function (Blueprint $table) {
            $table->dropColumn('tomorrow_schedule_sms_enabled');
        });
    }
};
