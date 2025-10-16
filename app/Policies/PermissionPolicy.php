<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function perform(User $user, string $action)
    {
        $actions = $user->roles->load("permissions")
            ->pluck("permissions")
            ->flatten()
            ->pluck("action");

        return $actions->contains($action);
    }
}
