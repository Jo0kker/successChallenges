<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Group $group): bool
    {
        return $group->canManage($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Group $group): bool
    {
        return $group->canManage($user);
    }

    /**
     * Determine whether the user can manage members of the group.
     */
    public function manageMembers(User $user, Group $group)
    {
        return $group->owner_id === $user->id ||
            $group->members()
            ->where('user_id', $user->id)
            ->where('role', 'moderator')
            ->exists();
    }

    /**
     * Determine whether the user can view the group.
     */
    public function view(User $user, Group $group): bool
    {
        return $group->isMember($user);
    }
}
