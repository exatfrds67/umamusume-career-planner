<?php

namespace App\Policies;

use App\Models\Career;
use App\Models\User;

class CareerPolicy
{
    /**
     * Perform pre-authorization checks.
     * Admin users can perform any action.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

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
    public function view(User $user, Career $career): bool
    {
        // User can view careers for their own characters
        return $career->character?->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Career $career): bool
    {
        // User can update careers for their own characters
        return $career->character?->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Career $career): bool
    {
        // User can delete careers for their own characters
        return $career->character?->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Career $career): bool
    {
        return $career->character?->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Career $career): bool
    {
        return $career->character?->user_id === $user->id;
    }
}
