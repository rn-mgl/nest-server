<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, "permission_role", "role_id", "permission_id");
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "role_user", "role_id", "user_id");
    }
}
