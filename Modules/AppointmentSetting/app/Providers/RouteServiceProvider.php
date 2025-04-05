<?php

namespace Modules\AppointmentSetting\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\User\Entities\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'Modules\AppointmentSetting\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapLivewireRoutes();
        $this->bindingModel();
    }
    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('AppointmentSetting', '/routes/web.php'));
    }
    protected function mapLivewireRoutes(): void
    {
        Route::middleware(['web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class, 'auth', 'admin'])
            ->prefix('admin')
            ->as('admin.')
            ->group(module_path('AppointmentSetting', '/routes/admin.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('AppointmentSetting', '/routes/api.php'));
    }
    public function bindingModel(): void
    {
        Route::model('segment', AppointmentSegment::class);
    }
}
