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

if (!function_exists('site_favicons')) {
    /**
     * Uploaded favicon paths of the current tenant keyed by size (null when not set).
     * Pass already loaded settings (like front $settingValues) to skip the query.
     */
    function site_favicons($settingValues = null): array
    {
        $keys = [
            '16x16' => Modules\Setting\Enum\SettingKeyEnum::FAVICON_16,
            '32x32' => Modules\Setting\Enum\SettingKeyEnum::FAVICON_32,
            '180x180' => Modules\Setting\Enum\SettingKeyEnum::FAVICON_APPLE_TOUCH,
        ];

        if ($settingValues === null) {
            try {
                $settingValues = app()->bound(\Stancl\Tenancy\Contracts\Tenant::class)
                    ? \Modules\Setting\Entities\Setting::getSettingByArray(array_values($keys))
                    : collect();
            } catch (\Throwable $e) {
                $settingValues = collect();
            }
        }

        $settingValues = collect($settingValues);

        return array_map(fn ($key) => \Modules\Setting\Entities\Setting::vc($settingValues, $key), $keys);
    }
}
