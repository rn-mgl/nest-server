<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReviewContent extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function review() {
        return $this->belongsTo(PerformanceReview::class);
    }
}
