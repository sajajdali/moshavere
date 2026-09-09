<?php

namespace Modules\AppointmentUser\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\User\Entities\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'Modules\AppointmentUser\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
        $this->bindingModel();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ])
            ->namespace($this->moduleNamespace)
            ->group(module_path('AppointmentUser', '/routes/web.php'));
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
            ->group(module_path('AppointmentUser', '/routes/api.php'));
    }

    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class, 'auth', 'admin'])
            ->prefix('admin')
            ->as('admin.')
            ->group(module_path('AppointmentUser', '/routes/admin.php'));
    }

    public function bindingModel(): void
    {
        Route::model('appointment_user', AppointmentUser::class);
        Route::model('sectionId', Service::class);
        Route::model('doctorId', User::class);
        Route::model('placeId', Place::class);
    }
}
