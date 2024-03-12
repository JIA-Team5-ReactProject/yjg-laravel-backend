<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class AbsenceListPolicy
{
    public function absenceCount(Admin $admin): bool
    {
        return $admin->admin_privilege;
    }

    public function reject(Admin $admin): bool
    {
        return $admin->admin_privilege;
    }
}
