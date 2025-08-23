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
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LeaveBalance>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
