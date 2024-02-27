<?php

namespace Modules\Place\app\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Place\app\Models\Place;
use Modules\User\Entities\User;

class PlacePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('place.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('place.create');
    }

    public function delete(User $user, Place $place): bool
    {
        return $user->hasPermissionTo('place.delete');
    }

    public function update(User $user, Place $place): bool
    {
        return $user->hasPermissionTo('place.edit');
    }
}
