<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    public function admin(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }
}
