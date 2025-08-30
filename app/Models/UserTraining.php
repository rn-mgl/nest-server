<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, "assigned_to", "id");
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, "assigned_by", "id");
    }

    public function training()
    {
        return $this->belongsTo(Training::class, "training_id", "id");
    }
}
