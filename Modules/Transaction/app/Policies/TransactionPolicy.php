<?php

namespace Modules\Transaction\app\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Transaction\app\Models\Transaction;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('transaction.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('transaction.create');
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->hasPermissionTo('transaction.delete');
    }

    public function update(User $user,  Transaction $transaction): bool
    {
        return $user->hasPermissionTo('transaction.edit');
    }
}
