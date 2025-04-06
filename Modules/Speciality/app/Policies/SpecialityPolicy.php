<?php

namespace Modules\Speciality\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Speciality\app\Models\Speciality;

class SpecialityPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('speciality');
    }
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('speciality.create');
    }
    public function update(User $user): bool
    {
        return $user->hasPermissionTo('speciality.update');
    }
    public function delete(User $user, Speciality $Speciality): bool
    {
        return $user->hasPermissionTo('speciality.delete');
    }
}
