<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AfterServicePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function updateStatus(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }
}
