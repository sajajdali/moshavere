<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\User\Enum\UserMetaEnum;

return new class extends Migration
{
    /**
     * کد ملی تا اینجا در جدول user_metas نگهداری می شد. از این پس ستون یکتای users.national_code منبع اصلی است.
     * فقط کدهای ده رقمی که بین کاربران تکراری نیستند منتقل می شوند؛ کدهای نامعتبر یا تکراری در user_metas
     * می مانند تا هیچ داده ای از بین نرود (User::national_code در صورت خالی بودن ستون از آنها می خواند).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'national_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('national_code', 10)->nullable()->after('mobile');
            });
        }

        $latest = [];
        DB::table('user_metas')
            ->where('meta_key', UserMetaEnum::NATIONAL_CODE->value)
            ->orderBy('id')
            ->select(['id', 'user_id', 'meta_value'])
            ->each(function ($meta) use (&$latest) {
                $code = strtr(trim((string) $meta->meta_value), [
                    '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                    '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                    '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                    '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
                ]);
                $latest[$meta->user_id] = $code;
            });

        $valid = array_filter($latest, fn ($code) => preg_match('/^\d{10}$/', $code) === 1);
        $owners = [];
        foreach ($valid as $userId => $code) {
            $owners[$code][] = $userId;
        }

        $duplicates = [];
        foreach ($owners as $code => $userIds) {
            if (count($userIds) > 1) {
                $duplicates[$code] = $userIds;
                continue;
            }
            DB::table('users')->where('id', $userIds[0])->update(['national_code' => $code]);
            DB::table('user_metas')
                ->where('user_id', $userIds[0])
                ->where('meta_key', UserMetaEnum::NATIONAL_CODE->value)
                ->delete();
        }

        if ($duplicates !== []) {
            Log::warning('users.national_code: duplicated national codes were left in user_metas', ['codes' => $duplicates]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('national_code');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'national_code')) {
            return;
        }

        DB::table('users')->whereNotNull('national_code')->orderBy('id')->each(function ($user) {
            DB::table('user_metas')->insert([
                'user_id' => $user->id,
                'meta_key' => UserMetaEnum::NATIONAL_CODE->value,
                'meta_value' => $user->national_code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['national_code']);
            $table->dropColumn('national_code');
        });
    }
};
