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

    public function acknowledgedBy()
    {
        return $this->belongsToMany(User::class, "user_onboarding_policy_acknowledgements", "policy_acknowledgement_id", "acknowledged_by");
    }
}
