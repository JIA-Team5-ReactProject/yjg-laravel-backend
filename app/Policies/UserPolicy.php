<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function admin(User $admin):bool
    {
        return $admin->admin;
    }

    public function master(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'master')->exists();
    }
}
