<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\GuestParticipant;

class GroupController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::with(['owner', 'members'])
            ->whereHas('members', function ($query) {
                $query->where('member_id', Auth::id())
                    ->where('member_type', User::class);
            })
            ->get();

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Groups/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => Auth::id(),
        ]);

        $group->members()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('groups.show', $group);
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        $tab = request()->query('tab', 'seasons');

        $data = [
            'group' => $group->load(['owner', 'members', 'guestMembers']),
            'activeTab' => $tab,
            'canManage' => $group->canManage(Auth::user()),
            'canManageMembers' => $group->canManageMembers(Auth::user()),
        ];

        if ($tab === 'seasons') {
            $data['seasons'] = $group->seasons()
                ->with(['challenges' => function ($query) {
                    $query->with('participants');
                }])
                ->get();
        } else {
            $members = $group->members()
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'type' => 'user',
                        'role' => $member->pivot->role ?: 'member'
                    ];
                });

            $guestMembers = $group->guestMembers()
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'type' => 'guest',
                        'role' => $member->pivot->role ?: 'member'
                    ];
                });

            $data['members'] = $members->concat($guestMembers);
        }

        return Inertia::render('Groups/Show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        $this->authorize('update', $group);

        return Inertia::render('Groups/Edit', [
            'group' => $group,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('groups.show', $group);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return redirect()->route('groups.index');
    }

    /**
     * Add a member to the group.
     */
    public function addMember(Request $request, Group $group)
    {
        $this->authorize('manageMembers', $group);

        \Log::info('Ajout de membre', [
            'group_id' => $group->id,
            'request' => $request->all()
        ]);

        $request->validate([
            'user_id' => 'nullable',
            'user_type' => 'required|in:user,guest',
            'role' => 'required|in:moderator,member',
            'name' => 'required_if:user_type,guest|string|max:255',
        ]);

        try {
            if ($request->user_type === 'guest') {
                // Créer un invité
                $guest = GuestParticipant::create([
                    'name' => $request->name,
                ]);

                $group->guestMembers()->attach($guest->id, [
                    'role' => $request->role
                ]);
            } else {
                // Ajouter un utilisateur existant
                $group->members()->attach($request->user_id, [
                    'role' => $request->role
                ]);
            }

            \Log::info('Membre ajouté avec succès');
            return redirect()->back()->with('success', 'Membre ajouté avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'ajout du membre', [
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout du membre');
        }
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Request $request, Group $group)
    {
        $this->authorize('manageMembers', $group);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group->members()->detach($request->user_id);

        return redirect()->back();
    }

    public function updateMemberRole(Request $request, Group $group, User $user)
    {
        $this->authorize('manageMembers', $group);

        $request->validate([
            'role' => 'required|in:moderator,member',
        ]);

        $group->members()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        return redirect()->back();
    }
}
