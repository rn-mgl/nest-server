<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
