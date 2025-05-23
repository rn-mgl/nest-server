<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contents() {
        return $this->hasMany(TrainingContent::class);
    }

    public function reviews() {
        return $this->hasMany(TrainingReview::class);
    }
}
