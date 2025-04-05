<?php

namespace Modules\Front\app\Policies;
use Modules\User\Entities\User;
use Modules\Discount\app\Models\Discount;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Front\app\Models\Faq;

class FaqPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Faq');
    }
    public function create(User $user, Faq $Faq): bool
    {
        return $user->hasPermissionTo('Faq.create');
    }
    public function update(User $user, Faq $Faq): bool
    {
        return $user->hasPermissionTo('Faq.update');
    }
    public function delete(User $user, Faq $Faq): bool
    {
        return $user->hasPermissionTo('Faq.delete');
    }
}
