<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    # Relationships #
    public function profilePictures()
    {
        return $this->morphMany(File::class, "fileable");
    }

    public function currentProfilePicture()
    {
        return $this->morphOne(File::class, "fileable")->latestOfMany();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, "user_id", "id");
    }

    /**
     * Summary of role
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Role, User>
     */
    public function role()
    {
        return $this->belongsTo(Role::class, "role_id", "id");
    }

    /**
     * Summary of leaveRequests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LeaveRequest, User>
     */
    public function createdLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, "requested_by", "id");
    }

    /**
     * Summary of assignedLeaveBalances
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LeaveBalance, User>
     */
    public function assignedLeaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, "assigned_to", "id");
    }

    /**
     * Summary of assignedOnboardings
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserOnboarding, User>
     */
    public function assignedOnboardings()
    {
        return $this->hasMany(UserOnboarding::class, "assigned_to", "id");
    }

    public function acknowledgedPolicies()
    {
        return $this->hasMany(UserOnboardingPolicyAcknowledgement::class, "acknowledged_by", "id");
    }

    /**
     * Summary of assignedPerformanceReviews
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserPerformanceReview, User>
     */
    public function assignedPerformanceReviews()
    {
        return $this->hasMany(UserPerformanceReview::class, "assigned_to", "id");
    }

    /**
     * Summary of assignedTrainings
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserTraining, User>
     */
    public function assignedTrainings()
    {
        return $this->hasMany(UserTraining::class, "user_training", "id");
    }

    # Scopes #

    /**
     * Local query scope to filter users by role
     */
    #[Scope]
    protected function ofRole(Builder $query, string $role)
    {
        $query->whereHas("role", function (Builder $query2) use ($role) {
            $query2->where("role", "=", $role);
        });
    }

}
