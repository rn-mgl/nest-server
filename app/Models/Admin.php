<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends User
{
    use HasFactory;

    protected $table = 'admins';
    protected $guarded = [];

    public function activities()
    {
        return $this->morphMany(Activity::class, 'activitable');
    }
}
