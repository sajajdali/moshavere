<?php

namespace Modules\AppointmentSetting\app\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\User\Entities\User;

class SegmentPolicy
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
        return $user->hasPermissionTo('segment');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('segment.create');
    }

    public function delete(User $user, AppointmentSegment $segment): bool
    {
        return $user->hasPermissionTo('segment.delete');
    }

    public function update(User $user, AppointmentSegment $segment): bool
    {
        return $user->hasPermissionTo('segment.edit');
    }
}
