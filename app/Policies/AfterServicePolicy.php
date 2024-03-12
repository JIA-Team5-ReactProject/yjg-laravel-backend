<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Afterservice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AfterServicePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function updateStatus(Admin $admin): bool
    {
        return $admin->admin_privilege;
    }
}
