<?php

namespace Modules\PractitionerApi\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Middleware\EnsureConsultationEnabled;
use Modules\PractitionerApi\Http\Middleware\ForceJsonResponse;
use Modules\PractitionerApi\Http\Middleware\AuthenticatePractitionerApi;
use Modules\PractitionerApi\Http\Middleware\EnsurePractitionerAppAccess;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        $tenantMiddleware = [
            'api',
            ForceJsonResponse::class,
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureConsultationEnabled::class,
        ];

        Route::prefix(config('practitionerapi.path', 'api/practitioner/v1'))
            ->middleware($tenantMiddleware)
            ->name('api.practitioner.v1.')
            ->group(module_path('PractitionerApi', 'Routes/api_v1_public.php'));

        Route::prefix(config('practitionerapi.path', 'api/practitioner/v1'))
            ->middleware(array_merge($tenantMiddleware, [
                AuthenticatePractitionerApi::class,
                EnsurePractitionerAppAccess::class,
            ]))
            ->name('api.practitioner.v1.')
            ->group(module_path('PractitionerApi', 'Routes/api_v1_authenticated.php'));
    }
}
