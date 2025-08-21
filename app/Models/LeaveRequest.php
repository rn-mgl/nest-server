<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Summary of leaves
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<LeaveType, LeaveRequest>
     */
    public function leaves()
    {
        return $this->belongsTo(LeaveType::class, "leave_type_id");
    }

    /**
     * Summary of requestedBy
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LeaveRequest>
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
