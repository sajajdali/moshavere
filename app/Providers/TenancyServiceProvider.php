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
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

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



    protected function handleTenantDatabaseSetup($tenant): void
    {
        // Define the preferred order of modules to be migrated and seeded
        $preferredOrder = ['User', 'Place', 'Service', 'Speciality', 'Setting', 'AppointmentSetting', 'AppointmentUser', 'Chat', 'Discount', 'Front', 'Reminder', 'Transaction', 'Absence'];

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
                $tenantMigrationPath = $module->getPath() . '/Database/Migrations/tenant';

                if (is_dir($tenantMigrationPath)) {
                    Artisan::call('migrate', [
                        '--path' => str_replace(base_path() . '/', '', $tenantMigrationPath),
                        '--force' => true,
                    ]);
                }
            }

            // Step 3: Seed each module's tenant-specific seeders
            foreach ($orderedModules as $module) {
                $seederPath = $module->getPath() . '/Database/Seeders';

                if (is_dir($seederPath)) {
                    $seederFiles = glob($seederPath . '/*.php');

                    foreach ($seederFiles as $seederFile) {
                        $className = pathinfo($seederFile, PATHINFO_FILENAME);
                        $fullClass = 'Modules\\' . $module->getName() . '\\Database\\Seeders\\' . $className;

                        if (class_exists($fullClass)) {
                            Artisan::call('db:seed', [
                                '--class' => $fullClass,
                                '--force' => true,
                            ]);
                        }
                    }
                }
            }
        });
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
            // Even higher priority than the initialization middleware
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
}
