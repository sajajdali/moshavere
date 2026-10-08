<?php

namespace Modules\Finance\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::middleware(['web', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
            ->group(module_path('Finance', 'routes/web.php'));
    }
}
