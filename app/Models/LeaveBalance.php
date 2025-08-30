<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Summary of leave
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<LeaveType, LeaveBalance>
     */
    public function leave()
    {
        return $this->belongsTo(LeaveType::class, "leave_type_id", "id");
    }

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LeaveBalance>
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, "assigned_to", "id");
    }

    public function providedBy()
    {
        return $this->belongsTo(User::class, "provided_by", "id");
    }
}
