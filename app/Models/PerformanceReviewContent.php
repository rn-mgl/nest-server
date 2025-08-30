<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReviewContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, "performance_review_id", "id");
    }
}
