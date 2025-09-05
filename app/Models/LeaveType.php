<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function requests()
    {
        return $this->hasMany(LeaveRequest::class, "leave_type_id", "id ");
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class, "leave_type_id", "id");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }
}
