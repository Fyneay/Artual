<?php

namespace App\Policies;

use App\Models\TypeSection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TypeSectionPolicy
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
    public function view(User $user, TypeSection $typeSection): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id == 1;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TypeSection $typeSection): bool
    {
        return $user->role_id == 1;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TypeSection $typeSection): bool
    {
        return $user->role_id == 1;
    }
}
