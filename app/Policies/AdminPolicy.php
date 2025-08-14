<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminPolicy
{
    /**
     * Create a new policy instance.
     */
    public function update(Admin $admin)
    {
        return Auth::user() instanceof Admin && Auth::id() === $admin->id;
    }
}
