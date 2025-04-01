<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOnboarding extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function assignedBy()
    {
        return $this->belongsTo(User::class, "assigned_by");
    }

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class);
    }
}
