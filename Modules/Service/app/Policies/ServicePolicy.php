<?php

namespace Modules\Service\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Service\app\Models\Service;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Service.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Service.create');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->hasPermissionTo('Service.delete');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->hasPermissionTo('Service.update');
    }
}
