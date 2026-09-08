<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_renew_cost')->default(0);
            $table->unsignedBigInteger('server_renew_cost')->default(0);
            $table->timestamps();
        });

        DB::table('central_settings')->insert([
            'support_renew_cost' => (int) config('app.tenant_renew_cost', 0),
            'server_renew_cost' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('central_settings');
    }
};
