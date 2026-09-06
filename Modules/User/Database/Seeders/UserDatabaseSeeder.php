<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Modules\User\Entities\User;
use Modules\User\Entities\UserMeta;
use Modules\User\Enum\UserMetaEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial user
        $user = User::firstOrCreate(
            ['mobile' => '0937602827'],
            [
                'email' => 'info@shemiranweb.test',
                'password' => ',!.[@Eh-IR[3gvjdVUz}9hLw:a#2VG?awdaDWArfRW90J209JR(@QJr6',
            ]
        );

        $user->metas()->updateOrCreate(
            ['meta_key' => UserMetaEnum::FIRST_NAME],
            ['meta_value' => 'پشتیبانی']
        );

        $user->metas()->updateOrCreate(
            ['meta_key' => UserMetaEnum::LAST_NAME],
            ['meta_value' => 'نوبت دهی']
        );

        // Create roles
        $roles = [
            'مدیر' => null,
            'بیمار' => null,
            'پزشک' => null,
            'اپراتور' => null,
            'منشی' => null,
            'ماما' => null,
        ];

        foreach ($roles as $name => &$role) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Create permissions
        $permissions = [
            'ADMIN_ACCESS',
            'SUPER_ADMIN',
            'DOCTOR',
            'SECRETERY',
            'USER_ACCESS',
            'USER_DEFAULT',
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

        $roles['پزشک']->syncPermissions([
            $permissionInstances['ADMIN_ACCESS'],
            $permissionInstances['DOCTOR'],
        ]);

        $roles['بیمار']->syncPermissions([
            $permissionInstances['USER_ACCESS'],
            $permissionInstances['USER_DEFAULT'],
        ]);

        $user->assignRole($roles['مدیر']);

        Artisan::call('auth:permission-sync');

        $secretaryPermissions = [
            'SECRETERY',
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
            'comment.own',
        ];

        $doctorPermissions = [
            'ADMIN_ACCESS',
            'DOCTOR',
            'appointment_user.own',
            'AppointmentSetting.own',
            'absence.own',
            'appointment_user.addApp',
            'appointment_user.edit',
            'appointment_user.delete',
            'appointment_user.list',
            'appointment_user.online',
            'appointment_user.message',
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

        foreach ($secretaryPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        foreach ($doctorPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles['اپراتور']->syncPermissions($secretaryPermissions);
        $roles['منشی']->syncPermissions($secretaryPermissions);
        $roles['پزشک']->syncPermissions($doctorPermissions);

        $mamaPermissions = [
            'ADMIN_ACCESS',
            'appointment_user',
            'appointment_user.online',
            'appointment_user.message',
        ];

        foreach ($mamaPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles['ماما']->syncPermissions($mamaPermissions);
    }
}
