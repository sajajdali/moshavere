<?php

namespace Modules\OnlineConsultation\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\OnlineConsultation\Http\Middleware\EnsureConsultationEnabled;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\OnlineConsultation\Console\DispatchConsultationSms;

class OnlineConsultationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'onlineconsultation');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) $this->commands([DispatchConsultationSms::class]);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'onlineconsultation');
        AppointmentUser::created(function (AppointmentUser $appointment) {
            if (tenancy()->initialized) app(AppointmentBillingService::class)->ensure($appointment);
        });
        $this->app->booted(function () {
            $tenant = [InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class];
            Route::middleware(array_merge(['web'], $tenant, ['auth', 'admin', EnsureConsultationEnabled::class, 'can:ONLINE_CONSULTATION_MANAGE']))
                ->prefix('admin/online-consultation')->name('admin.consultation.')
                ->group(__DIR__.'/../routes/admin.php');
            foreach (config('tenancy.central_domains', []) as $domain) {
                Route::domain($domain)->middleware(['web', 'auth', 'admin', 'prevent-tenant', 'can:SUPER_ADMIN'])
                    ->prefix('central/online-consultation')->name('central.consultation.')
                    ->group(__DIR__.'/../routes/central.php');
            }
            Route::middleware(array_merge(['api'], $tenant, ['auth:sanctum', EnsureConsultationEnabled::class]))
                ->prefix('api/online-consultation')->name('api.consultation.')
                ->group(__DIR__.'/../routes/api.php');
        });
    }
}
