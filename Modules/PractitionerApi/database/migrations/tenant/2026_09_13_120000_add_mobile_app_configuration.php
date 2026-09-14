<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->json('app_landing_content')->nullable()->after('patient_instructions');
        });

        Schema::table('consultation_practitioners', function (Blueprint $table): void {
            $table->json('app_settings')->nullable()->after('weekly_schedule');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->dropColumn('app_landing_content');
        });

        Schema::table('consultation_practitioners', function (Blueprint $table): void {
            $table->dropColumn('app_settings');
        });
    }
};
