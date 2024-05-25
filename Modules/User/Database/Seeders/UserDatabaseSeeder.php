<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\User\Entities\UserMeta;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create first user factory
        $user = User::create([
            'mobile' => '09197729101',
            'email' => 'info@jesmino.test',
            'password' => '123',
        ]);

        $user->metas()->saveMany([
            new UserMeta([
                'meta_key' => UserMetaEnum::FIRST_NAME,
                'meta_value' => 'مدیر',
            ]),
            new UserMeta([
                'meta_key' => UserMetaEnum::LAST_NAME,
                'meta_value' => 'کل',
            ]),
        ]);

        // Create roles
        $role = Role::create(['name' => 'مدیر']);
        $userDefaultRole = Role::create(['name' => 'بیمار']);
        $doctorsRoles = Role::create(['name' => 'پزشک']);
        $opdatorRoles = Role::create(['name' => 'اپراتور']);
        $secretaryRoles = Role::create(['name' => 'منشی']);
        $mamaRoles = Role::create(['name' => 'ماما']);

        // Create permissions
        $adminPermission = Permission::create(['name' => 'ADMIN_ACCESS']);
        $superAdminPermission = Permission::create(['name' => 'SUPER_ADMIN']);
        $doctorPermission = Permission::create(['name' => 'DOCTOR']);
        $secretaryPermission = Permission::create(['name' => 'SECRETERY']);
        $userPermission = Permission::create(['name' => 'USER_ACCESS']);
        $userDefaultPermission = Permission::create(['name' => 'USER_DEFAULT']);

        // Assign permissions to roles
        $role->givePermissionTo([$adminPermission, $superAdminPermission]);
        $doctorsRoles->givePermissionTo([$adminPermission, $doctorPermission]);

        // Assign User essential permissions to کاربران Role
        $userDefaultRole->givePermissionTo([$userPermission, $userDefaultPermission]);

        // Assign مدیر Role to first user factory (assuming $user is defined)
        $user->assignRole($role);

        // Run permission synchronization command
        Artisan::call('auth:permission-sync');

        // Sync permissions for secretery Role
        $secretaryPermissions  = [
            $adminPermission,
            $secretaryPermission,
            'appointment_user',
            'appointment_user.addApp',
            'appointment_user.edit',
            'appointment_user.delete',
            'appointment_user.list',
            'appointment_user.online',
            'appointment_user.message',
            'absence',
            'absence.create',
            'absence.delete',
            'admin.dashboard',
            'admin.dashboard.appointments',
            'admin.dashboard.analytic',
            'AppointmentSetting',
            'AppointmentSetting.update',
            'chat',
            'user',
            'user.create',
            'user.edit',
            'user.delete',
            'user.documentte',
            'appointment_user.feedBack',
        ];
        $opdatorRoles->syncPermissions($secretaryPermissions);
        $secretaryRoles->syncPermissions($secretaryPermissions);
        // Sync permissions for mama Role
        $mamaPermissions = [$adminPermission, 'appointment_user', 'appointment_user.online', 'appointment_user.message'];
        $mamaRoles->syncPermissions($mamaPermissions);
    }
}
