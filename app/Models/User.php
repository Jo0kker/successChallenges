<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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

    public function groups()
    {
        return $this->morphedByMany(Group::class, 'member', 'group_members', 'member_id', 'group_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the groups owned by this user.
     */
    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    /**
     * Get the challenges where this user failed.
     */
    public function failedChallenges(): MorphMany
    {
        return $this->morphMany(Challenge::class, 'failed_by');
    }

    /**
     * Get the challenges where this user is a participant.
     */
    public function participatedChallenges(): MorphToMany
    {
        return $this->morphedByMany(
            Challenge::class,
            'participant',
            'challenge_participants'
        )->withTimestamps();
    }
}
