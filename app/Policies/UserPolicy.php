<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can ban the model.
     */
    public function ban(User $user, User $model): bool
    {
        // Admins can ban users, but not themselves or other admins
        return $user->is_admin && $user->id !== $model->id && ! $model->is_admin;
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, User $model): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can promote the model to admin.
     */
    public function promote(User $user, User $model): bool
    {
        // Only admins can promote, and cannot demote themselves
        return $user->is_admin && $user->id !== $model->id;
    }
}
