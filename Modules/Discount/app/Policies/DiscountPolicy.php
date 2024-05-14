<?php

namespace Modules\Discount\app\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Discount\app\Models\Discount;

class DiscountPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('discount');
    }
    public function update(User $user): bool
    {
        return $user->hasPermissionTo('discount.update');
    }
    public function delete(User $user, Discount $discount): bool
    {
        return $user->hasPermissionTo('discount.delete');
    }
}
