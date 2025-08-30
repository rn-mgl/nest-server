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
    public function leave()
    {
        return $this->belongsTo(LeaveType::class, "leave_type_id", "id");
    }

    /**
     * Summary of requestedBy
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LeaveRequest>
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, "requested_by", "id");
    }

    /**
     * Summary of approvedBy
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LeaveRequest>
     */
    public function actionedBy()
    {
        return $this->belongsTo(User::class, "actioned_by", "id");
    }
}
