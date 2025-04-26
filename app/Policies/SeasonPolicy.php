<?php

namespace App\Policies;

use App\Models\Season;
use App\Models\User;
use App\Models\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class SeasonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the season.
     */
    public function view(User $user, Season $season, Group $group): bool
    {
        return $group->isMember($user);
    }

    /**
     * Determine whether the user can create seasons.
     */
    public function create(User $user, Group $group): bool
    {
        return $group->isModerator($user) || $group->isAdmin($user) || $group->isOwner($user);
    }

    /**
     * Determine whether the user can update the season.
     */
    public function update(User $user, Season $season, Group $group): bool
    {
        return $group->isModerator($user) || $group->isAdmin($user) || $group->isOwner($user);
    }

    /**
     * Determine whether the user can delete the season.
     */
    public function delete(User $user, Season $season, Group $group): bool
    {
        return $group->isAdmin($user) || $group->isOwner($user);
    }

    /**
     * Determine whether the user can manage the season.
     */
    public function manage(User $user, Season $season, Group $group): bool
    {
        return $group->isModerator($user) || $group->isAdmin($user) || $group->isOwner($user);
    }
}
