<?php

namespace Database\Seeders\central;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial user
        $user = User::firstOrCreate(
            ['mobile' => '09122978167'],
            [
                'email' => 'info@central.test',
                'password' => '123',
            ]
        );

        $user->metas()->updateOrCreate(
            ['meta_key' => UserMetaEnum::FIRST_NAME],
            ['meta_value' => 'مدیر']
        );

        $user->metas()->updateOrCreate(
            ['meta_key' => UserMetaEnum::LAST_NAME],
            ['meta_value' => 'کل']
        );

        // Create roles
        $roles = [
            'مدیر' => null,
        ];

        foreach ($roles as $name => &$role) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Create permissions
        $permissions = [
            'ADMIN_ACCESS',
            'SUPER_ADMIN',
        ];

        $permissionInstances = [];
        foreach ($permissions as $perm) {
            $permissionInstances[$perm] = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $roles['مدیر']->syncPermissions([
            $permissionInstances['ADMIN_ACCESS'],
            $permissionInstances['SUPER_ADMIN'],
        ]);


        $user->assignRole($roles['مدیر']);

        Artisan::call('auth:permission-sync');

        $mamaPermissions = [
            'ADMIN_ACCESS',
            'appointment_user',
            'appointment_user.online',
            'appointment_user.message',
        ];

        foreach ($mamaPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

    }

}
