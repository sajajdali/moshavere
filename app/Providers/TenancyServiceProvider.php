<?php

declare(strict_types=1);

namespace App\Providers;

use Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire;
use Livewire\Features\SupportFileUploads\FilePreviewController;
use Module;
use ReflectionClass;
use RuntimeException;
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
            // Run all tenant migrations together so Laravel can sort dependencies by migration name.
            Artisan::call('migrate', config('tenancy.migration_parameters'));

            // Step 2: Seed each module's tenant-specific seeders
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
                            require_once $seederFile;

                            $seederClass = collect(get_declared_classes())->first(function (string $class) use ($seederFile) {
                                if (!is_subclass_of($class, Seeder::class)) {
                                    return false;
                                }

                                return realpath((new ReflectionClass($class))->getFileName()) === realpath($seederFile);
                            });

                            if ($seederClass === null) {
                                throw new RuntimeException("No seeder class was found in [$seederFile].");
                            }

                            Artisan::call('db:seed', [
                                '--class' => $seederClass,
                                '--force' => true,
                            ]);
                        }
                        break;
                    }
                }
            }
        });
    }
}
