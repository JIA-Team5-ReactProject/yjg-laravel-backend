<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingRoomPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }
}
