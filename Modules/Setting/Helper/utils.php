<?php

use Illuminate\Support\Facades\Schema;

if (!function_exists('setting')) {
    function setting(?Modules\Setting\Enum\SettingKeyEnum $key)
    {
        if ($key === null) {
            return null;
        }

        if (!app()->bound(\Stancl\Tenancy\Contracts\Tenant::class)) {
            return null;
        }

        try {
            if (!Schema::hasTable('settings')) {
                return null;
            }

            return \Modules\Setting\Entities\Setting::v($key);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
