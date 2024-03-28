<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AfterServiceCommentPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }
}
