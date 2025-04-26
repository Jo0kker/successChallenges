<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SeasonController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created season in storage.
     */
    public function store(Request $request, Group $group)
    {
        $this->authorize('create', [Season::class, $group]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,active,completed',
        ]);

        $season = $group->seasons()->create($validated);

        return redirect()->route('seasons.show', [$group, $season])
            ->with('success', 'Saison créée avec succès.');
    }

    /**
     * Update the specified season in storage.
     */
    public function update(Request $request, Group $group, Season $season)
    {
        $this->authorize('update', [$season, $group]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,active,completed',
        ]);

        try {
            $season->setStatus($validated['status']);
            unset($validated['status']);
            $season->update($validated);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('seasons.show', [$group, $season])
            ->with('success', 'Saison mise à jour avec succès.');
    }

    /**
     * Remove the specified season from storage.
     */
    public function destroy(Group $group, Season $season)
    {
        $this->authorize('delete', [$season, $group]);
        $season->delete();

        return redirect()->route('groups.show', $group)
            ->with('success', 'Saison supprimée avec succès !');
    }

    /**
     * Display the specified season.
     */
    public function show(Group $group, Season $season)
    {
        $this->authorize('view', [$season, $group]);

        return Inertia::render('Seasons/Show', [
            'group' => $group->load(['owner', 'members']),
            'season' => $season->load(['challenges.participants', 'challenges.failedBy']),
            'canManage' => $group->isModerator(Auth::user()) || $group->isAdmin(Auth::user()) || $group->isOwner(Auth::user()),
            'canAddChallenges' => $season->status === 'active',
        ]);
    }

    public function create(Group $group)
    {
        $this->authorize('create', [Season::class, $group]);

        return Inertia::render('Seasons/Create', [
            'group' => $group->load('owner'),
        ]);
    }

    public function start(Group $group, Season $season)
    {
        $this->authorize('manage', [$season, $group]);

        try {
            $season->setStatus('active');
            return back()->with('success', 'La saison a été démarrée avec succès.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(Group $group, Season $season)
    {
        $this->authorize('manage', [$season, $group]);

        try {
            $season->setStatus('completed');
            return back()->with('success', 'La saison a été terminée avec succès.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Group $group, Season $season)
    {
        $this->authorize('update', [$season, $group]);

        return Inertia::render('Seasons/Edit', [
            'group' => $group->load('owner'),
            'season' => $season,
        ]);
    }
}
