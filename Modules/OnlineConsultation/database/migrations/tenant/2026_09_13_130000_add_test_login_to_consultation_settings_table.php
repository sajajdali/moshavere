<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->boolean('test_login_enabled')->default(false)->after('app_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->dropColumn('test_login_enabled');
        });
    }
};
