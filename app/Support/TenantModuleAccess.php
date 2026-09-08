<?php

namespace App\Support;

class TenantModuleAccess
{
    public const CORE_MODULES = ['Admin', 'Front', 'Setting', 'User'];

    public static function enabled(string $module): bool
    {
        if (! tenancy()->initialized) {
            return true;
        }

        if (in_array($module, self::CORE_MODULES, true)) {
            return true;
        }

        $enabledModules = tenant('enabled_modules');

        // Existing tenants created before module selection retain their current behavior.
        if (! is_array($enabledModules)) {
            return true;
        }

        return in_array($module, $enabledModules, true);
    }
}
