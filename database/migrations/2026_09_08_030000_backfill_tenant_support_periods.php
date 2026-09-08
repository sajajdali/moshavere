<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')->orderBy('id')->each(function ($tenant): void {
            $startedAt = $tenant->support_started_at ?: $tenant->created_at ?: now();

            DB::table('tenants')->where('id', $tenant->id)->update([
                'support_started_at' => $startedAt,
                'expires_at' => $tenant->expires_at ?: Carbon::parse($startedAt)->addYear(),
            ]);
        });
    }

    public function down(): void
    {
        // Existing support dates are business data and must not be erased.
    }
};
