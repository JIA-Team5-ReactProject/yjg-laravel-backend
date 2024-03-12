<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalonServicePolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(Admin $admin): bool
    {
        return $admin->salon_privilege;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin): bool
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
