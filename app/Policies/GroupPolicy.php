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
            ->where('member_id', $user->id)
            ->where('member_type', User::class)
            ->where('role', 'moderator')
            ->exists();
    }

    /**
     * Determine whether the user can view the group.
     */
    public function view(User $user, Group $group): bool
    {
        $membersQuery = $group->members()
            ->wherePivot('member_id', $user->id)
            ->wherePivot('member_type', User::class);

        \Log::info('Vérification de l\'accès au groupe', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'is_owner' => $group->owner_id === $user->id,
            'members' => $group->members()->get()->toArray(),
            'member_exists' => $membersQuery->exists(),
            'sql_query' => $membersQuery->toSql(),
            'bindings' => $membersQuery->getBindings()
        ]);

        // L'utilisateur peut voir le groupe s'il est le propriétaire
        if ($group->owner_id === $user->id) {
            return true;
        }

        // Ou s'il est membre du groupe
        return $membersQuery->exists();
    }
}
