<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'member', 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function guestMembers(): MorphToMany
    {
        return $this->morphToMany(GuestParticipant::class, 'member', 'group_members', 'group_id', 'member_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function isOwner(User $user): bool
    {
        return (int)$this->owner_id === (int)$user->id;
    }

    public function isAdmin(User $user)
    {
        return $this->members()
            ->wherePivot('role', 'admin')
            ->where('member_id', $user->id)
            ->where('member_type', User::class)
            ->exists();
    }

    public function isMember(User $user): bool
    {
        return $this->members()
            ->wherePivot('member_id', $user->id)
            ->wherePivot('member_type', User::class)
            ->exists();
    }

    public function isModerator(User $user): bool
    {
        return $this->members()
            ->where('member_id', $user->id)
            ->where('member_type', User::class)
            ->where('role', 'moderator')
            ->exists();
    }

    public function canManage(User $user): bool
    {
        return (int)$this->owner_id === (int)$user->id || $this->isModerator($user);
    }

    public function canManageMembers(User $user): bool
    {
        return $this->owner_id === $user->id || $this->isModerator($user);
    }

    public function allMembers()
    {
        $members = collect();

        // Ajouter les utilisateurs
        $members = $members->concat($this->members()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'user',
                'points' => 0,
                'failed_challenges' => 0,
                'participated_challenges' => 0
            ];
        }));

        // Ajouter les invités
        $members = $members->concat($this->guestMembers()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'guest',
                'points' => 0,
                'failed_challenges' => 0,
                'participated_challenges' => 0
            ];
        }));

        return $members;
    }

    public function calculateRanking()
    {
        $ranking = $this->allMembers();

        // Calculer les points pour chaque défi de la saison active
        $activeSeason = $this->seasons()->where('status', 'active')->first();
        if ($activeSeason) {
            foreach ($activeSeason->challenges as $challenge) {
                // Points pour les participants
                foreach ($challenge->participants as $participant) {
                    $member = $ranking->first(function ($m) use ($participant) {
                        return $m['id'] == $participant->id &&
                            $m['type'] == ($participant instanceof GuestParticipant ? 'guest' : 'user');
                    });
                    if ($member) {
                        $member['points'] += $challenge->bet_amount;
                        $member['participated_challenges']++;
                    }
                }

                // Points négatifs pour celui qui a échoué
                if ($challenge->failedBy) {
                    $failedMember = $ranking->first(function ($m) use ($challenge) {
                        return $m['id'] == $challenge->failedBy->id &&
                            $m['type'] == ($challenge->failedBy instanceof GuestParticipant ? 'guest' : 'user');
                    });
                    if ($failedMember) {
                        $failedMember['points'] -= $challenge->bet_amount;
                        $failedMember['failed_challenges']++;
                    }
                }
            }
        }

        return $ranking->sortByDesc('points')->values();
    }

    public function activeSeason()
    {
        return $this->seasons()->where('status', 'active')->first();
    }
}
