<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOnboarding extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, UserOnboarding>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Summary of onboarding
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Onboarding, UserOnboarding>
     */
    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class);
    }
}
