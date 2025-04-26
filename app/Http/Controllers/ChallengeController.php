<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Season;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;

class ChallengeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the specified challenge.
     */
    public function show(Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('view', [$challenge, $season, $group]);

        return Inertia::render('Challenges/Show', [
            'group' => $group->load('owner'),
            'season' => $season,
            'challenge' => $challenge->load(['participants', 'failedBy']),
        ]);
    }

    /**
     * Show the form for creating a new challenge.
     */
    public function create(Group $group, Season $season)
    {
        $this->authorize('create', [Challenge::class, $season, $group]);

        $successList = [
            'Dofus Émeraude',
            'Dofus Pourpre',
            'Dofus Turquoise',
            'Dofus Ivoire',
            'Dofus Cawotte',
            'Dofus Ebène',
            'Dofus Ocre',
            'Dofus Incarnam',
            'Dofus Vulbis',
            'Dofus Ochre',
            'Dofus Abyssal',
            'Dofus Cobalt',
            'Dofus Argenté',
            'Dofus Doré',
            'Dofus Saphir',
            'Dofus Rubis',
            'Dofus Améthyste',
            'Dofus Topaze',
            'Dofus Perle',
            'Dofus Opale',
            'Dofus Jade',
            'Dofus Obsidienne',
            'Dofus Cristal',
            'Dofus Diamant',
        ];

        return Inertia::render('Challenges/Create', [
            'group' => $group,
            'season' => $season,
            'groupMembers' => $group->members()->get(),
            'successList' => $successList,
        ]);
    }

    /**
     * Store a newly created challenge in storage.
     */
    public function store(Request $request, Group $group, Season $season)
    {
        $this->authorize('create', [Challenge::class, $season, $group]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bet_amount' => 'required|numeric|min:0',
            'failed_by' => 'required|exists:users,id',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $challenge = $season->challenges()->create([
            'season_id' => $season->id,
            'name' => $validated['name'],
            'bet_amount' => $validated['bet_amount'],
            'failed_by' => $validated['failed_by'],
        ]);

        // Ajouter les participants
        $challenge->participants()->attach($validated['participants']);

        return redirect()->route('seasons.show', [$group, $season])
            ->with('success', 'Défi créé avec succès !');
    }

    public function markAsFailed(Request $request, Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('update', [$challenge, $season, $group]);

        $validated = $request->validate([
            'failed_by' => 'required|exists:users,id',
        ]);

        $challenge->markAsFailed(User::find($validated['failed_by']));

        return redirect()->back()->with('success', 'Le défi a été marqué comme échoué.');
    }

    public function index(Group $group, Season $season, Request $request)
    {
        $this->authorize('view', [$season, $group]);

        $query = $season->challenges()
            ->with(['participants' => function ($query) {
                $query->select('users.id', 'users.name');
            }, 'failedBy']);

        $member = null;
        if ($request->has('member')) {
            $member = User::find($request->member);
            if ($member) {
                $query->where(function ($query) use ($member) {
                    // Défis où l'utilisateur est participant
                    $query->whereHas('participants', function ($query) use ($member) {
                        $query->where('users.id', $member->id);
                    })
                        // OU défis où l'utilisateur a échoué
                        ->orWhere('failed_by', $member->id);
                });
            }
        }

        $challenges = $query->get();

        return Inertia::render('Challenges/Index', [
            'group' => $group->load('owner'),
            'season' => $season,
            'challenges' => $challenges,
            'member' => $member,
            'canManage' => auth()->user()->can('manage', [$season, $group])
        ]);
    }

    public function destroy(Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('delete', [$challenge, $group]);

        try {
            $challenge->delete();
            return redirect()->route('seasons.show', [$group->id, $season->id])
                ->with('success', 'Défi supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('seasons.show', [$group->id, $season->id])
                ->with('error', 'Erreur lors de la suppression du défi.');
        }
    }
}
