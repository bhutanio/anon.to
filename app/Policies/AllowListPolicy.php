<?php

namespace App\Policies;

use App\Models\AllowList;
use App\Models\User;

class AllowListPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AllowList $allowList): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AllowList $allowList): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can import rules.
     */
    public function import(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can export rules.
     */
    public function export(User $user): bool
    {
        return $user->is_admin;
    }
}
