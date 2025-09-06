<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function content()
    {
        return $this->morphOne(File::class, "fileable");
    }

    public function training()
    {
        return $this->belongsTo(Training::class, "training_id", "id");
    }
}
