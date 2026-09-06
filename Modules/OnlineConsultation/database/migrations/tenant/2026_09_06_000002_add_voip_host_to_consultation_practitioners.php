<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_practitioners') && ! Schema::hasColumn('consultation_practitioners', 'voip_host')) {
            Schema::table('consultation_practitioners', function (Blueprint $table) {
                $table->string('voip_host')->nullable()->after('extension');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consultation_practitioners') && Schema::hasColumn('consultation_practitioners', 'voip_host')) {
            Schema::table('consultation_practitioners', function (Blueprint $table) {
                $table->dropColumn('voip_host');
            });
        }
    }
};
