<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function updateHR(User $user)
    {
        $guard = Auth::guard("base");
        $currentUser = $guard->user();

        return $currentUser instanceof User &&
                $currentUser->role === "hr" &&
                $guard->id() === $user->id;
    }
}
