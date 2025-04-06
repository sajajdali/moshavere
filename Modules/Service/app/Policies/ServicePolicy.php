<?php

namespace Modules\service\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\service\app\Models\service;

class servicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('service');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('service.create');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->hasPermissionTo('service.delete');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->hasPermissionTo('service.update');
    }
}
