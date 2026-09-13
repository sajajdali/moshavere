<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table) {
            $table->text('voip_call_token')->nullable()->after('voip_secret');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table) {
            $table->dropColumn('voip_call_token');
        });
    }
};
