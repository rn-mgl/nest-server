<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, "users");
    }
}
