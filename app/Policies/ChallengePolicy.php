<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use App\Models\Group;
use App\Models\Season;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChallengePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the challenge.
     */
    public function view(User $user, Challenge $challenge, Season $season, Group $group): bool
    {
        return $group->isMember($user);
    }

    /**
     * Determine whether the user can create challenges.
     */
    public function create(User $user, Season $season, Group $group): bool
    {
        return $group->isMember($user);
    }

    /**
     * Determine whether the user can update the challenge.
     */
    public function update(User $user, Challenge $challenge, Season $season, Group $group): bool
    {
        return $group->isModerator($user);
    }

    /**
     * Determine whether the user can delete the challenge.
     */
    public function delete(User $user, Challenge $challenge, Group $group)
    {
        return $group->isModerator($user) || $group->isAdmin($user) || $group->isOwner($user);
    }
}
