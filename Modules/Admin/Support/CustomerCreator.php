<?php

namespace Modules\Admin\Support;

use Illuminate\Support\Facades\DB;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;

class CustomerCreator
{
    public static function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'mobile' => $data['mobile'],
                'email' => filled($data['email'] ?? null) ? $data['email'] : null,
                'password' => $data['password'],
                'center_name' => $data['center_name'],
            ]);

            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];

            $customerRole = Role::firstOrCreate(['name' => 'مشتری', 'guard_name' => 'web']);
            $user->assignRole($customerRole);

            return $user->refresh();
        });
    }
}
