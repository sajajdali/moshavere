<?php

declare(strict_types=1);

namespace App\Providers;

use Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire;
use Livewire\Features\SupportFileUploads\FilePreviewController;
use Module;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function register()
    {
        //
    }

    public function boot()
    {
        Event::listen(DatabaseCreated::class, fn($event) => $this->handleTenantDatabaseSetup($event->tenant));

        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();

        Livewire::setUpdateRoute(function ($handle) {
            $middleware = ['web'];

            if (!in_array(request()->getHost(), config('tenancy.central_domains', []))) {
                $middleware[] = \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class;
                $middleware[] = \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class;
            }

            return Route::post('/livewire/update', $handle)->middleware($middleware);
        });

        FilePreviewController::$middleware = ['web', 'universal', InitializeTenancyByDomain::class];
    }

    public function events()
    {
        return [
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    // حذف Jobs\SeedDatabase::class برای جلوگیری از اجرای زودهنگام
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],
        ];
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }
                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }

    protected function handleTenantDatabaseSetup($tenant): void
    {
        $preferredOrder = ['User', 'Service','Place', 'Speciality', 'Setting', 'AppointmentSetting', 'AppointmentUser', 'Chat', 'Discount', 'Front', 'Reminder', 'Transaction', 'Absence' , 'API'];

        $orderedModules = collect($preferredOrder)->map(function ($name) {
            return Module::find($name);
        })->filter();

        $tenant->run(function () use ($orderedModules) {
            // Step 1: Run base tenant migrations
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Step 2: Run each module's tenant-specific migrations
            foreach ($orderedModules as $module) {
                $possiblePaths = [
                    $module->getPath() . '/Database/Migrations/tenant',
                    $module->getPath() . '/database/migrations/tenant',
                ];

                foreach ($possiblePaths as $tenantMigrationPath) {
                    if (is_dir($tenantMigrationPath)) {
                        Artisan::call('migrate', [
                            '--path' => str_replace(base_path() . '/', '', $tenantMigrationPath),
                            '--force' => true,
                        ]);
                        break;
                    }
                }
            }

            // Step 3: Seed each module's tenant-specific seeders
            foreach ($orderedModules as $module) {
                $possibleSeederPaths = [
                    $module->getPath() . '/Database/Seeders',
                    $module->getPath() . '/database/seeders',
                    $module->getPath() . '/database/Seeders',
                    $module->getPath() . '/Database/seeders',
                ];

                foreach ($possibleSeederPaths as $seederPath) {
                    if (is_dir($seederPath)) {
                        $seederFiles = glob($seederPath . '/*.php');

                        foreach ($seederFiles as $seederFile) {
                            $className = pathinfo($seederFile, PATHINFO_FILENAME);

                            $namespaceParts = explode('/', str_replace(base_path() . '/', '', $seederPath));
                            $namespaceParts = array_map(fn($part) => ucfirst($part), $namespaceParts);
                            $moduleNamespace = 'Modules\\' . $module->getName();
                            $subNamespace = implode('\\', array_slice($namespaceParts, 2));
                            $fullClass = $moduleNamespace . '\\' . $subNamespace . '\\' . $className;

                            if (class_exists($fullClass)) {
                                Artisan::call('db:seed', [
                                    '--class' => $fullClass,
                                    '--force' => true,
                                ]);
                            }
                        }
                        break;
                    }
                }
            }
        });
    }
}
