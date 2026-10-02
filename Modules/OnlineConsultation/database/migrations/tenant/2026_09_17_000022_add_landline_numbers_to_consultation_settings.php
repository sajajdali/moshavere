<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->text('call_center_landline_numbers')->nullable()->after('call_center_number');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->dropColumn('call_center_landline_numbers');
        });
    }
};
