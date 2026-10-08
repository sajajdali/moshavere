<?php

namespace Modules\Front\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\Front\app\Http\Middleware\RedirectToLoginForVoipOnlyAppointments;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'Modules\Front\Http\Controllers';

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
        $this->maplivewireRoutes();
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
            ->group(module_path('Front', '/routes/web.php'));
    }
    protected function maplivewireRoutes(): void
    {
        $host = request()->getHost();
        $tenantId = null;
        if (! app()->runningInConsole()) {
            $tenantId = \App\Models\Domain::where('domain', $host)->first()?->tenant_id;
        }

        $tenantRoutePath = module_path('Front', 'Tenants/'.$tenantId.'/routes/livewire.php');

        // انتخاب قالب در تنظیمات، تعیین کننده است و بر مسیرهای اختصاصی تننت اولویت دارد؛
        // در غیر این صورت مسیرهای اختصاصی تننت و سپس مسیرهای پیش فرض لایوایر استفاده میشوند.
        if ($this->newTemplateEnabledFor($tenantId)) {
            $routes = module_path('Front', '/routes/newapp.php');
        } elseif (file_exists($tenantRoutePath)) {
            $routes = $tenantRoutePath;
        } else {
            $routes = module_path('Front', '/routes/livewire.php');
        }

        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            RedirectToLoginForVoipOnlyAppointments::class,
        ])
            ->group($routes);
    }
    /**
     * آیا برای این تننت قالب جدید فعال است.
     * در زمان ثبت مسیرها هنوز تننت مقداردهی نشده، بنابراین مقدار تنظیمات
     * مستقیم از دیتابیس همان تننت خوانده و برای همان درخواست کش میشود.
     */
    protected function newTemplateEnabledFor(?string $tenantId): bool
    {
        if ($tenantId === null) {
            return false;
        }

        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (! $tenant) {
                return false;
            }

            // نام دیتابیس روی خود تننت ذخیره شده و ممکن است با prefix پیش فرض یکی نباشد
            $database = $tenant->database()->getName();
            // اتصال 'tenant' خودش پویاست، پس از اتصال مرکزی به عنوان الگو استفاده میشود
            $template = config('tenancy.database.template_tenant_connection')
                ?: config('tenancy.database.central_connection', 'mysql');

            // یک اتصال موقت به دیتابیس همین تننت، بدون تغییر وضعیت tenancy
            config([
                'database.connections.front_template_check' => array_merge(
                    config('database.connections.'.$template),
                    ['database' => $database],
                ),
            ]);

            $value = \Illuminate\Support\Facades\DB::connection('front_template_check')
                ->table('settings')
                ->where('setting_key', \Modules\Setting\Enum\SettingKeyEnum::USE_NEW_TEMPLATE->value)
                ->value('setting_value');

            \Illuminate\Support\Facades\DB::purge('front_template_check');

            return filter_var($value, FILTER_VALIDATE_BOOL);
        } catch (\Throwable $e) {
            // اگر دیتابیس یا جدول تنظیمات در دسترس نبود، قالب قدیم استفاده میشود
            return false;
        }
    }

    protected function mapAdminRoutes(): void
    {
        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
         'auth', 'admin'])
            ->prefix('admin')
            ->as('admin.')
            ->group(module_path('Front', '/routes/admin.php'));
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
            ->group(module_path('Front', '/routes/api.php'));
    }
}
