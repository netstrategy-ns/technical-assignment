<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{

    public function accessAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function updateAsAdmin(User $authenticated, User $target): bool
    {
        return $authenticated->isAdmin();
    }

    public function viewAnyInAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewInAdmin(User $user, User $target): bool
    {
        return $user->isAdmin();
    }
}
