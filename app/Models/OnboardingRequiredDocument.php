<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingRequiredDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Summary of onboarding
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Onboarding, OnboardingRequiredDocument>
     */
    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class, "onboarding_id", "id");
    }
}
