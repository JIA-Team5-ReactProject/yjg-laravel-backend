<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SalonReservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalonReservationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin): bool
    {
        return $admin->salon_privilege;
    }
}
