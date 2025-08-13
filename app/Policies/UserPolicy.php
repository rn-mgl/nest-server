<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function update(User $user)
    {
        $guard = Auth::guard("ba");
        $currentUser = $guard->user();

        return $currentUser instanceof User &&
                $currentUser->role === "hr" &&
                $guard->id() === $user->id;
    }
}
