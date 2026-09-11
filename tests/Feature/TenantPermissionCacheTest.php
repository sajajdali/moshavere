<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Tenancy\Bootstrappers\PrefixCacheTenancyBootstrapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantPermissionCacheTest extends TestCase
{
    public function test_file_permission_cache_is_isolated_and_reloaded_when_tenant_changes(): void
    {
        $directory = sys_get_temp_dir().'/tenant-permission-test-'.bin2hex(random_bytes(8));
        config([
            'cache.default' => 'file',
            'cache.stores.file.path' => $directory,
            'permission.cache.store' => 'default',
            'permission.cache.key' => 'test.permission.cache',
        ]);
        app('cache')->forgetDriver();
        $registrar = app(PermissionRegistrar::class);
        $registrar->clearPermissionsCollection();
        $registrar->initializeCache();

        try {
            foreach (['permission_alpha' => 1, 'permission_beta' => 2] as $connection => $adminId) {
                config(['database.connections.'.$connection => [
                    'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
                ]]);
                DB::setDefaultConnection($connection);
                (require base_path('database/migrations/2024_01_30_115541_create_permission_tables.php'))->up();
                DB::table('permissions')->insert([
                    ['id' => $adminId, 'name' => 'ADMIN_ACCESS', 'guard_name' => 'web'],
                    ['id' => 3 - $adminId, 'name' => 'SUPER_ADMIN', 'guard_name' => 'web'],
                ]);
            }

            DB::setDefaultConnection('permission_alpha');
            $this->assertSame(1, Permission::findByName('ADMIN_ACCESS')->id);
            $bootstrapper = new PrefixCacheTenancyBootstrapper($this->app);
            $bootstrapper->bootstrap(new Tenant(['id' => 'alpha']));
            $alphaKey = $registrar->cacheKey;
            $this->assertSame(1, Permission::findByName('ADMIN_ACCESS')->id);

            DB::setDefaultConnection('permission_beta');
            $bootstrapper->bootstrap(new Tenant(['id' => 'beta']));
            $this->assertNotSame($alphaKey, $registrar->cacheKey);
            $this->assertSame(2, Permission::findByName('ADMIN_ACCESS')->id);
            $this->assertSame(1, Permission::findByName('SUPER_ADMIN')->id);

            DB::setDefaultConnection('permission_alpha');
            $bootstrapper->bootstrap(new Tenant(['id' => 'alpha']));
            $this->assertSame($alphaKey, $registrar->cacheKey);
            $this->assertSame(1, Permission::findByName('ADMIN_ACCESS')->id);
            $this->assertSame(2, Permission::findByName('SUPER_ADMIN')->id);

            $bootstrapper->revert();
            $this->assertSame('test.permission.cache', $registrar->cacheKey);
            $this->assertSame(1, Permission::findByName('ADMIN_ACCESS')->id);
        } finally {
            File::deleteDirectory($directory);
            DB::purge('permission_alpha');
            DB::purge('permission_beta');
        }
    }
}
