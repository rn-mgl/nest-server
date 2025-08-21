<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Summary of contents
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PerformanceReviewContent, PerformanceReview>
     */
    public function contents()
    {
        return $this->hasMany(PerformanceReviewContent::class);
    }

    /**
     * Summary of assignedUsers
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User, PerformanceReview, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, "user_performance_review", "performance_review_id", "user_id");
    }
}
