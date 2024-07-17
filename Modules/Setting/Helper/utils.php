<?php

use Illuminate\Support\Facades\Schema;

if (!function_exists('setting')) {
    function setting(?Modules\Setting\Enum\SettingKeyEnum $key)
    {
        // If $key is null, return null
        if ($key === null) {
            return null;
        }
        // Check if the table exists
        if (!Schema::hasTable('settings')) {
            return null;
        }
        return \Modules\Setting\Entities\Setting::v($key);
    }
}
