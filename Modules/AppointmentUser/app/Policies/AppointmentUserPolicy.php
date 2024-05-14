<?php

namespace Modules\AppointmentUser\app\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\Entities\User;

class AppointmentUserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {

        return $user->hasPermissionTo('appointment_user');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('appointment_user.addApp');
    }

    public function delete(User $user, AppointmentUser $appointmentUser): bool
    {

        return $user->hasPermissionTo('appointment_user.delete');
    }

    public function update(User $user, AppointmentUser $appointmentUser): bool
    {
        return $user->hasPermissionTo('appointment_user.edit');
    }

}
