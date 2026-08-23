<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voip_voice_records', function (Blueprint $table) {
            $table->timestamp('listened_at')->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('voip_voice_records', function (Blueprint $table) {
            $table->dropColumn('listened_at');
        });
    }
};
