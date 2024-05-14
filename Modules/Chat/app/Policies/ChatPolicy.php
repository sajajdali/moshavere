<?php

namespace Modules\Chat\app\Policies;

use Modules\User\Entities\User;
use Modules\Chat\app\Models\Chat;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('appointment_user');
    }
    public function update(User $user, Chat $chat): bool
    {
        return $user->hasPermissionTo('appointment_user.edit');
    }
}
