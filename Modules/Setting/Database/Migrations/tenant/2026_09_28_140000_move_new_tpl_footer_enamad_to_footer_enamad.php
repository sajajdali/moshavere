<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * کد اینماد قالب جدید (543) به تنظیم مشترک FOOTER_ENAMAD (415) منتقل میشود.
 * اگر FOOTER_ENAMAD از قبل مقدار داشته باشد، همان مقدار حفظ میشود.
 */
return new class extends Migration
{
    private const OLD_KEY = 543;

    private const NEW_KEY = 415;

    public function up(): void
    {
        $oldValue = DB::table('settings')->where('setting_key', self::OLD_KEY)->value('setting_value');

        if (filled($oldValue)) {
            $current = DB::table('settings')->where('setting_key', self::NEW_KEY)->value('setting_value');

            if (blank($current)) {
                DB::table('settings')->updateOrInsert(
                    ['setting_key' => self::NEW_KEY],
                    ['setting_value' => $oldValue],
                );
            }
        }

        DB::table('settings')->where('setting_key', self::OLD_KEY)->delete();
    }

    public function down(): void
    {
        //
    }
};
