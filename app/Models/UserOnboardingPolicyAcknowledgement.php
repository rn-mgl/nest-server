<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserOnboardingPolicyAcknowledgement extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function policy()
    {
        return $this->belongsTo(OnboardingPolicyAcknowledgement::class, "policy_acknowledgement_id", "id");
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, "acknowledged_by", "id");
    }
}
