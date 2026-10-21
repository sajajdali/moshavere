<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ترتیب نمایش پزشکان در صفحه ثبت نوبت و تنظیمات زمان های حضور.
     * ستون خالی یعنی پزشک هنوز جایی در ترتیب ندارد و بعد از پزشکان مرتب شده نمایش داده می شود.
     * پزشکان فعلی به ترتیب شناسه شماره گذاری می شوند.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'display_order')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('display_order')->nullable()->index();
            });
        }

        $position = 1;
        DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', \Modules\User\Entities\User::class);
            })
            ->where('model_has_roles.role_id', 3)
            ->orderBy('users.id')
            ->pluck('users.id')
            ->each(function ($id) use (&$position) {
                DB::table('users')->where('id', $id)->update(['display_order' => $position++]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'display_order')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['display_order']);
            $table->dropColumn('display_order');
        });
    }
};
