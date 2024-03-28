<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function unregisterMaster(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'master')->exists()) {
            return true;
        }
        return false;

    }
}
