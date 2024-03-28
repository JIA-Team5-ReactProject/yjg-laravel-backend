<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingRoomReservationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function reject(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }
}
