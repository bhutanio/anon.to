<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
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
    public function view(User $user, Report $report): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can resolve the report.
     */
    public function resolve(User $user, Report $report): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can dismiss the report.
     */
    public function dismiss(User $user, Report $report): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can add notes to the report.
     */
    public function addNotes(User $user, Report $report): bool
    {
        return $user->is_admin;
    }
}
