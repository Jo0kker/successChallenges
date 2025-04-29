<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\GuestParticipant;

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
        $this->authorize('view', arguments: [$season, $group]);

        // Charger les défis avec leurs relations
        $season = $season->load([
            'challenges' => function ($query) {
                $query->with([
                    'participants' => function ($query) {
                        $query->select('users.id as user_id', 'users.name as user_name');
                    },
                    'guestParticipants' => function ($query) {
                        $query->select('guest_participants.id as guest_id', 'guest_participants.name as guest_name');
                    },
                    'failedBy' => function ($query) {
                        $query->select('id', 'name');
                    }
                ]);
            }
        ]);

        // Initialiser le classement avec tous les membres
        $ranking = [];

        // Ajouter les membres utilisateurs
        foreach ($group->members as $member) {
            Log::info('Ajout membre utilisateur au classement', [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'user'
            ]);
            $ranking[] = [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'user',
                'points' => 0,
                'gains' => 0,
                'pertes' => 0,
                'participated_challenges' => 0,
                'failed_challenges' => 0
            ];
        }

        // Ajouter les membres invités
        foreach ($group->guestMembers as $member) {
            Log::info('Ajout membre invité au classement', [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'guest'
            ]);
            $ranking[] = [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'guest',
                'points' => 0,
                'gains' => 0,
                'pertes' => 0,
                'participated_challenges' => 0,
                'failed_challenges' => 0
            ];
        }

        // Calculer les points pour chaque défi
        foreach ($season->challenges as $challenge) {
            Log::info('Traitement du défi', [
                'challenge_id' => $challenge->id,
                'challenge_name' => $challenge->name,
                'participants_count' => $challenge->participants->count(),
                'guest_participants_count' => $challenge->guestParticipants->count()
            ]);

            $betAmount = $challenge->bet_amount;
            $totalParticipants = $challenge->participants->count() + $challenge->guestParticipants->count();

            // Points pour les participants
            foreach ($challenge->participants as $participant) {
                $memberIndex = array_search($participant->user_id, array_column($ranking, 'id'));
                if ($memberIndex !== false) {
                    Log::info('Ajout points pour participant utilisateur', [
                        'participant_id' => $participant->user_id,
                        'points' => $betAmount
                    ]);
                    $ranking[$memberIndex]['points'] += $betAmount;
                    $ranking[$memberIndex]['gains'] += $betAmount;
                    $ranking[$memberIndex]['participated_challenges']++;
                }
            }

            foreach ($challenge->guestParticipants as $participant) {
                $memberIndex = array_search($participant->guest_id, array_column($ranking, 'id'));
                if ($memberIndex !== false) {
                    Log::info('Ajout points pour participant invité', [
                        'participant_id' => $participant->guest_id,
                        'points' => $betAmount
                    ]);
                    $ranking[$memberIndex]['points'] += $betAmount;
                    $ranking[$memberIndex]['gains'] += $betAmount;
                    $ranking[$memberIndex]['participated_challenges']++;
                }
            }

            // Points négatifs pour celui qui a échoué
            if ($challenge->failedBy) {
                $failedMemberIndex = array_search($challenge->failedBy->id, array_column($ranking, 'id'));
                if ($failedMemberIndex !== false) {
                    $penalty = $betAmount * $totalParticipants; // Pénalité = dette * nombre de participants
                    Log::info('Ajout pénalité pour échec', [
                        'failed_by_id' => $challenge->failedBy->id,
                        'penalty' => $penalty
                    ]);
                    $ranking[$failedMemberIndex]['points'] -= $penalty;
                    $ranking[$failedMemberIndex]['pertes'] += $penalty;
                    $ranking[$failedMemberIndex]['failed_challenges']++;
                }
            }
        }

        // Trier le classement par points
        usort($ranking, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        // Formater le classement pour la vue
        $ranking = array_map(function ($member) {
            return [
                'id' => $member['id'],
                'name' => $member['name'],
                'type' => $member['type'],
                'total' => number_format($member['points'], 0, ',', ' '),
                'gains' => number_format($member['gains'], 0, ',', ' '),
                'pertes' => number_format($member['pertes'], 0, ',', ' '),
                'victoires' => $member['participated_challenges'],
                'defis_echoues' => $member['failed_challenges']
            ];
        }, $ranking);

        Log::info('Classement final', [
            'ranking' => $ranking
        ]);

        // Formater les défis pour la vue
        $formattedChallenges = $season->challenges->map(function ($challenge) {
            return [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'bet_amount' => $challenge->bet_amount,
                'failed_by' => $challenge->failedBy ? [
                    'id' => $challenge->failedBy->id,
                    'name' => $challenge->failedBy->name,
                    'type' => class_basename($challenge->failedBy)
                ] : null,
                'participants' => $challenge->participants->map(function ($participant) {
                    return [
                        'id' => $participant->user_id,
                        'name' => $participant->user_name,
                        'type' => 'user'
                    ];
                }),
                'guest_participants' => $challenge->guestParticipants->map(function ($participant) {
                    return [
                        'id' => $participant->guest_id,
                        'name' => $participant->guest_name,
                        'type' => 'guest'
                    ];
                })
            ];
        });

        return Inertia::render('Seasons/Show', [
            'group' => $group,
            'season' => $season,
            'challenges' => $formattedChallenges,
            'ranking' => $ranking,
            'canManage' => Gate::allows('manage', [$season, $group]),
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
