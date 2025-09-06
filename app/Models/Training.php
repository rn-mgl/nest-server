<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function certificate()
    {
        return $this->morphOne(File::class, "fileable");
    }

    /**
     * Summary of contents
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TrainingContent, Training>
     */
    public function contents()
    {
        return $this->hasMany(TrainingContent::class, "training_id", "id");
    }

    /**
     * Summary of reviews
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TrainingReview, Training>
     */
    public function reviews()
    {
        return $this->hasMany(TrainingReview::class, "training_id", "id");
    }

}
