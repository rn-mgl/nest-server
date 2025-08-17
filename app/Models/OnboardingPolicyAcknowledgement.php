<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingPolicyAcknowledgement extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Summary of onboarding
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Onboarding, OnboardingPolicyAcknowledgement>
     */
    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class, "onboarding_id", "id");
    }

    public function acknowledgedBy()
    {
        return $this->belongsToMany(User::class, "onboarding_policy_acknowledgement_user", "policy_acknowledgement_id", "user_id");
    }
}
