<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReviewSurvey extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function performanceReview()
    {
        return $this->belongsTo(PerformanceReview::class, "performance_review_id", "id");
    }

    public function userResponse()
    {
        return $this->hasOne(UserPerformanceReviewResponse::class, "performance_review_survey_id", "id");
    }
}
