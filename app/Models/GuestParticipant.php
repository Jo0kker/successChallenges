<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class GuestParticipant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the challenges where this guest participant failed.
     */
    public function failedChallenges(): MorphMany
    {
        return $this->morphMany(Challenge::class, 'failed_by');
    }

    /**
     * Get the challenges where this guest participant is a participant.
     */
    public function participatedChallenges(): MorphToMany
    {
        return $this->morphedByMany(
            Challenge::class,
            'participant',
            'challenge_participants'
        )->withTimestamps();
    }

    public function groups(): MorphToMany
    {
        return $this->morphedByMany(Group::class, 'member', 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }
}
