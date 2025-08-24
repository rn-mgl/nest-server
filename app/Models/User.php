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

    /**
     * Summary of activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Activity, User>
     */
    public function activities()
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    /**
     * Get role of the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Role, User>
     */
    public function roles()
    {
        return $this->hasOne(Role::class, "id", "role_id");
    }

    /**
     * Summary of leaveRequests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LeaveRequest, User>
     */
    public function createdLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, "user_id", "id");
    }

    /**
     * Summary of assignedOnboardings
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserOnboarding, User>
     */
    public function assignedOnboardings()
    {
        return $this->hasMany(UserOnboarding::class, "user_id", "id");
    }

    /**
     * Summary of assignedPerformanceReviews
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<PerformanceReview, User, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function assignedPerformanceReviews()
    {
        return $this->belongsToMany(PerformanceReview::class, "user_performance_reviews", "user_id", "performance_review_id");
    }

    /**
     * Summary of assignedTrainings
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Training, User, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function assignedTrainings()
    {
        return $this->belongsToMany(Training::class, "user_trainings", "user_id", "training_id");
    }

    # Scopes #

    /**
     * Local query scope to filter users by role
     */
    #[Scope]
    protected function ofRole(Builder $query, string $role)
    {
        $query->whereHas("roles", function (Builder $query2) use ($role) {
            $query2->where("role", "=", $role);
        });
    }

}
