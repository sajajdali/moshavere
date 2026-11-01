<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * لینک های اشتراکی (مثل لینک خدمت مشخص پزشک) زیاد باز می شوند؛
     * tinyInteger در ۱۲۷ بازدید سرریز می کرد.
     * details مقصد لینک را نگه می دارد تا آدرس هنگام باز شدن، متناسب با قالب فعال ساخته شود.
     */
    public function up(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->unsignedInteger('number_visited')->default(0)->change();
            $table->json('details')->nullable()->after('link_url');
            $table->index('link_code');
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropIndex(['link_code']);
            $table->dropColumn('details');
            $table->tinyInteger('number_visited')->default(0)->change();
        });
    }
};
