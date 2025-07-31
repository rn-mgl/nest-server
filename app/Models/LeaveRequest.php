<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function leaves()
    {
        return $this->belongsTo(LeaveType::class, "leave_type_id");
    }
}
