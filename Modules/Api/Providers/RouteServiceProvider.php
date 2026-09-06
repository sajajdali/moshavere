<?php

namespace Modules\Api\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Api\Http\Controllers';
    private $apiVersion = 'v1';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {

        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Api', '/Routes/api_v1_public.php'));

        Route::prefix('api/v1')
            ->middleware(['api', 'auth:sanctum'])
            ->namespace($this->moduleNamespace)
            ->group(module_path('Api', '/Routes/api_v1_user.php'));

        Route::prefix('api/v1')
            ->middleware([
                'api',
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                \Modules\Api\app\Http\Middleware\LogVoipRequest::class,
                'basicAuth',
            ])
            ->namespace($this->moduleNamespace)
            ->group(module_path('Api', '/Routes/api_v1_voip.php'));
    }
}
