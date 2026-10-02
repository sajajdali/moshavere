<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->string('softphone_server_address')->nullable()->after('voip_host');
        });

        DB::table('consultation_settings')
            ->whereNull('softphone_server_address')
            ->update(['softphone_server_address' => DB::raw('voip_host')]);
    }

    public function down(): void
    {
        Schema::table('consultation_settings', function (Blueprint $table): void {
            $table->dropColumn('softphone_server_address');
        });
    }
};
