<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Link $link): bool
    {
        // Allow owners to view their own links regardless of status
        if ($user !== null && $link->user_id === $user->id) {
            return true;
        }

        // For non-owners, the link must be active
        if ($link->is_active) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Anyone can create links (anonymous or authenticated)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Link $link): bool
    {
        // Only the owner can update their link
        return $link->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Link $link): bool
    {
        // Only the owner can delete their link
        return $link->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Link $link): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Link $link): bool
    {
        // Only the owner can permanently delete their link
        return $link->user_id === $user->id;
    }

    /**
     * Determine whether the admin can force view any link.
     */
    public function forceView(User $user, Link $link): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the admin can force delete any link.
     */
    public function adminDelete(User $user, Link $link): bool
    {
        return $user->is_admin;
    }
}
