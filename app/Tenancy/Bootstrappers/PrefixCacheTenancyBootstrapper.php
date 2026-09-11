<?php

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;

class PrefixCacheTenancyBootstrapper implements TenancyBootstrapper
{
    private string $originalPrefix;
    private string $originalPermissionCacheKey;

    public function __construct(private Application $app)
    {
        $this->originalPrefix = (string) $app['config']['cache.prefix'];
        $this->originalPermissionCacheKey = (string) $app['config']['permission.cache.key'];
    }

    public function bootstrap(Tenant $tenant): void
    {
        $this->setPrefix($this->originalPrefix . 'tenant_' . $tenant->getTenantKey() . '_');
        $this->setPermissionCacheKey($this->originalPermissionCacheKey . '.tenant.' . $tenant->getTenantKey());
    }

    public function revert(): void
    {
        $this->setPrefix($this->originalPrefix);
        $this->setPermissionCacheKey($this->originalPermissionCacheKey);
    }

    private function setPermissionCacheKey(string $key): void
    {
        // File cache stores ignore cache.prefix. The permission key itself must
        // identify the tenant, and the singleton must drop the previous tenant's collection/store.
        $this->app['config']['permission.cache.key'] = $key;
        $registrar = $this->app->make(PermissionRegistrar::class);
        $registrar->clearPermissionsCollection();
        $registrar->initializeCache();
    }

    private function setPrefix(string $prefix): void
    {
        $this->app['config']['cache.prefix'] = $prefix;

        // Rebuild the selected store so it receives the updated tenant prefix.
        $this->app['cache']->forgetDriver();
        Cache::clearResolvedInstances();
    }
}
