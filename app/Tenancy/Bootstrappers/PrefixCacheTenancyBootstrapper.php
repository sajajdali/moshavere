<?php

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;

class PrefixCacheTenancyBootstrapper implements TenancyBootstrapper
{
    private string $originalPrefix;

    public function __construct(private Application $app)
    {
        $this->originalPrefix = (string) $app['config']['cache.prefix'];
    }

    public function bootstrap(Tenant $tenant): void
    {
        $this->setPrefix($this->originalPrefix . 'tenant_' . $tenant->getTenantKey() . '_');
    }

    public function revert(): void
    {
        $this->setPrefix($this->originalPrefix);
    }

    private function setPrefix(string $prefix): void
    {
        $this->app['config']['cache.prefix'] = $prefix;

        // Rebuild the selected store so it receives the updated tenant prefix.
        $this->app['cache']->forgetDriver();
        Cache::clearResolvedInstances();
    }
}
