<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingPolicyAcknowledgement extends Model
{
    use HasFactory, SoftDeletes;

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

    public function userAcknowledgement()
    {
        return $this->hasOne(UserOnboardingPolicyAcknowledgement::class, "policy_acknowledgement_id", "id");
    }
}
