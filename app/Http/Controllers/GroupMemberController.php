<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GroupMemberController extends Controller
{
    public function updateRole(Request $request, Group $group, User $user)
    {
        Gate::authorize('manage-members', $group);

        $request->validate([
            'role' => ['required', 'in:moderator,member'],
        ]);

        $group->members()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        return back();
    }
}
