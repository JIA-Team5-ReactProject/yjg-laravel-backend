<?php

namespace App\Policies;

use App\Models\User;

class SalonPolicy
{
    /**
     * Create a new policy instance.
     */
    public function salon(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }
}
