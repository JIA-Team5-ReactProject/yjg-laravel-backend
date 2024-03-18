<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SalonBreakTime;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalonBreakTimePolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(Admin $admin): bool
    {
        return $admin->salon_privilege;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(Admin $admin): bool
    {
        return $admin->salon_privilege;
    }
}
