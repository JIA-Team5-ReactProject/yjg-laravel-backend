<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalonReservationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'salon')->exists();
    }
}
