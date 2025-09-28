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
     * Summary of surveys
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PerformanceReviewSurvey, PerformanceReview>
     */
    public function surveys()
    {
        return $this->hasMany(PerformanceReviewSurvey::class, "performance_review_id", "id");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }

}
