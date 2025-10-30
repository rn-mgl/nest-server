<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{

    public function read(User $authorizedUser, User $requestedUser)
    {
        return $authorizedUser->id === $requestedUser->id;
    }

    public function update(User $authorizedUser, User $requestedUser)
    {
        return $authorizedUser->id === $requestedUser->id;
    }
}
