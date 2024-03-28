<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsenceListPolicy
{
    public function absenceCount(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }

    public function reject(User $admin): bool
    {
        return $admin->admin && $admin->privileges()->where('privilege', 'admin')->exists();
    }
}
