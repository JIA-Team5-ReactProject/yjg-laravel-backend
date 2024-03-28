<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalonBreakTimePolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'salon')->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'salon')->exists();
    }
}
