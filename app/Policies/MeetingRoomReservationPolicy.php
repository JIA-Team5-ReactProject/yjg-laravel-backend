<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\MeetingRoomReservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingRoomReservationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function reject(Admin $admin): bool
    {
        return $admin->admin_privilege;
    }
}
