<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
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
    public function view(?User $user, Note $note): bool
    {
        // Allow viewing if note is active and not expired
        if ($note->is_active && ($note->expires_at === null || $note->expires_at->isFuture())) {
            return true;
        }

        // Allow owners to view their own notes even if expired
        if ($user !== null && $note->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Anyone can create notes (anonymous or authenticated)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        // Notes are immutable in MVP
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        // Only the owner can delete their note
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        // Only the owner can permanently delete their note
        return $note->user_id === $user->id;
    }
}
