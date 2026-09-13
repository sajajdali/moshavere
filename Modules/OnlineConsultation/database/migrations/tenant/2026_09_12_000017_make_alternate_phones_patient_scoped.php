<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointment_alternate_phones')) return;

        $connection = DB::connection();
        $prefix = $connection->getTablePrefix();
        $table = $prefix.'appointment_alternate_phones';
        $appointments = $prefix.'appointment_users';

        if ($connection->getDriverName() === 'mysql') {
            $oldForeign = $table.'_appointment_id_foreign';
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$oldForeign}`");
            DB::statement("ALTER TABLE `{$table}` MODIFY `appointment_id` BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `alt_phone_source_appt_fk` FOREIGN KEY (`appointment_id`) REFERENCES `{$appointments}` (`id`) ON DELETE SET NULL");
        }
    }

    public function down(): void
    {
        // The source appointment is audit information only. Reverting to cascade
        // could delete a patient's permanent phone, so this migration is one-way.
    }
};
