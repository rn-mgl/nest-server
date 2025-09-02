<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function training()
    {
        return $this->belongsTo(Training::class, "training_id", "id");
    }

    public function userResponse()
    {
        return $this->hasOne(UserTrainingReviewResponse::class, "training_review_id", "id");
    }
}
