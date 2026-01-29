<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;

class CharacterPolicy
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
    public function view(User $user, Character $character): bool
    {
        // Seeded characters can be viewed by any authenticated user
        if ($character->is_seeded) {
            return true;
        }

        // User-created characters can only be viewed by their owner
        return $user->id === $character->user_id;
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
    public function update(User $user, Character $character): bool
    {
        // Seeded characters can be updated by any authenticated user
        if ($character->is_seeded) {
            return true;
        }

        // User-created characters can only be updated by their owner
        return $user->id === $character->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Character $character): bool
    {
        // Seeded characters cannot be deleted by regular users
        if ($character->is_seeded) {
            return false;
        }

        // User-created characters can only be deleted by their owner
        return $user->id === $character->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Character $character): bool
    {
        return $user->id === $character->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Character $character): bool
    {
        return $user->id === $character->user_id;
    }
}
