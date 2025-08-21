<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Summary of contents
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TrainingContent, Training>
     */
    public function contents() {
        return $this->hasMany(TrainingContent::class);
    }

    /**
     * Summary of reviews
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TrainingReview, Training>
     */
    public function reviews() {
        return $this->hasMany(TrainingReview::class);
    }

    /**
     * Summary of assignedUsers
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User, Training, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, "user_trainings", "training_id", "user_id");
    }
}
