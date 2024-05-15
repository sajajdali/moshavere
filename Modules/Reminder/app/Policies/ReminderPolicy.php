<?php

namespace Modules\Reminder\app\Policies;

use Modules\User\Entities\User;
use Modules\Reminder\app\Models\Reminder;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderPolicy
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
        return $user->hasPermissionTo('reminder');
    }
    public function update(User $user): bool
    {
        return $user->hasPermissionTo('reminder.update');
    }
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reminder.create');
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $user->hasPermissionTo('reminder.delete');
    }
}
