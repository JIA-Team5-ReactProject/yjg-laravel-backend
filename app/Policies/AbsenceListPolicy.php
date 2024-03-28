<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsenceListPolicy
{
    public function absenceCount(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }

    public function reject(User $admin): bool
    {
        if($admin->privileges()->where('privilege', 'admin')->exists()) {
            return true;
        }
        return false;
    }
}
