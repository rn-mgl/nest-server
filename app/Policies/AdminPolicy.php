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
        return Auth::guard("admin")->user() instanceof Admin && Auth::guard("admin")->id() === $admin->id;
    }
}
