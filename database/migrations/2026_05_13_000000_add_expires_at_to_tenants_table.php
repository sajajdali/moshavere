<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('created_at');
        });

        DB::table('tenants')
            ->select(['id', 'created_at'])
            ->whereNull('expires_at')
            ->orderBy('id')
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    DB::table('tenants')
                        ->where('id', $tenant->id)
                        ->update([
                            'expires_at' => Carbon::parse($tenant->created_at)->addYear(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
