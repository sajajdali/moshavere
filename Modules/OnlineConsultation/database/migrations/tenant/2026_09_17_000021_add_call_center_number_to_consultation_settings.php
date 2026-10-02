<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->string('call_center_number', 30)->nullable()->after('queue_number');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->dropColumn('call_center_number');
        });
    }
};
