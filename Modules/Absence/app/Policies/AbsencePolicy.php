<?php

namespace Modules\Absence\app\Policies;

use Modules\User\Entities\User;
use Modules\Absence\app\Models\Absence;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsencePolicy
{
    use HandlesAuthorization;


    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('absence');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('absence.create');
    }

    public function delete(User $user, Absence $absence): bool
    {
        return $user->hasPermissionTo('absence.delete');
    }

}
