<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\GuestParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

        // Charger les relations nécessaires avec les pivots
        $group->load([
            'owner',
            'members' => function ($query) {
                $query->withPivot('role');
            },
            'guestMembers' => function ($query) {
                $query->withPivot('role');
            }
        ]);

        $data = [
            'group' => $group,
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
            // Charger les membres et les invités séparément
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

            $guests = $group->guestMembers()
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'type' => 'guest',
                        'role' => $member->pivot->role ?: 'member'
                    ];
                });

            $data['members'] = $members->concat($guests);
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

        Log::info('Ajout de membre', [
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
                    'role' => 'member',
                    'member_type' => GuestParticipant::class,
                    'member_id' => $guest->id
                ]);
            } else {
                // Ajouter un utilisateur existant
                $group->members()->attach($request->user_id, [
                    'role' => $request->role,
                    'member_type' => User::class,
                    'member_id' => $request->user_id
                ]);
            }

            Log::info('Membre ajouté avec succès');
            return redirect()->back()->with('success', 'Membre ajouté avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout du membre', [
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
            'user_id' => 'required',
            'user_type' => 'required|in:user,guest',
        ]);

        try {
            if ($request->user_type === 'guest') {
                $group->guestMembers()->detach($request->user_id);
            } else {
                $group->members()->detach($request->user_id);
            }

            return redirect()->back()->with('success', 'Membre retiré avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du membre', [
                'error' => $e->getMessage(),
                'group_id' => $group->id,
                'user_id' => $request->user_id,
                'user_type' => $request->user_type
            ]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression du membre');
        }
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
