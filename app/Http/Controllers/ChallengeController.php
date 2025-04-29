<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Season;
use App\Models\Challenge;
use App\Models\GuestParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreChallengeRequest;

class ChallengeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the specified challenge.
     */
    public function show(Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('view', [$challenge, $season, $group]);

        $challenge->load(['participants', 'guestParticipants', 'failedBy']);

        $formattedChallenge = [
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
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'type' => 'user'
                ];
            }),
            'guest_participants' => $challenge->guestParticipants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'type' => 'guest'
                ];
            })
        ];

        return Inertia::render('Challenges/Show', [
            'group' => $group->load('owner'),
            'season' => $season,
            'challenge' => $formattedChallenge,
            'canManage' => Gate::allows('manage', [$season, $group])
        ]);
    }

    /**
     * Show the form for creating a new challenge.
     */
    public function create(Group $group, Season $season)
    {
        $this->authorize('view', [$season, $group]);

        // Récupérer les membres utilisateurs
        $userMembers = $group->members()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'user',
                'role' => $member->pivot->role
            ];
        });

        // Récupérer les membres invités
        $guestMembers = $group->guestMembers()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'guest',
                'role' => $member->pivot->role
            ];
        });

        // Fusionner les deux collections
        $allMembers = $userMembers->concat($guestMembers);

        return Inertia::render('Challenges/Create', [
            'group' => $group->load('owner'),
            'season' => $season,
            'groupMembers' => $allMembers,
            'successList' => [
                'Ami des Bombombres',
                'Anti-kopiage',
                'Armure des vents',
                'Barbare',
                'Blitzkrieg',
                'Bombombragogo',
                'Chacun son monstre',
                'Collant',
                'Combat rapproché',
                'Combat à l\'aveuglette',
                'Conquérant',
                'Contre vent contraire',
                'Courber l\'épine',
                'D\'une pierre deux kouï',
                'De la même lignée',
                'Dernier',
                'Deux pour le prix d\'un',
                'Distance de sécurité',
                'Domakurobligeance',
                'Domakuropitmisation',
                'Dorigamise à mort',
                'Dorigamiséricorde',
                'Doume',
                'Duel',
                'Désordonné',
                'En ligne de mire',
                'En ligne et contre tous',
                'Feu l\'artifice',
                'Focus',
                'Hardi',
                'Images du monde flottant',
                'Imprévisible',
                'Infatigable',
                'Intouchable',
                'J\'en ai fait un pin\'s',
                'J\'y suis, j\'y reste',
                'Kaokuriche',
                'Kwaboom 2 en 1',
                'La contre-poterie',
                'La Danse du Lion',
                'La diagonale du plein',
                'Lantervalle',
                'Le jardin que pierre a bâti',
                'Le Kang-Fu d\'abord',
                'Le Kin-Fu d\'abord',
                'Le Sein-Supplice',
                'Les petits d\'abord',
                'Liaison dangereuse',
                'Lotus et bouche cousue',
                'Lotus pour un',
                'Lune Vengeresse',
                'Légitime défense',
                'M et Mme Samovar ont un fils...',
                'Madurasse Masque',
                'Mains propres',
                'Martyr',
                'Maître kopiste',
                'Mille Pétales de Cerisier',
                'Miroir',
                'Misanthrope',
                'Mutinerie de l\'Akakwa',
                'Mutinerie de la Kokom',
                'Mutinerie du Betto',
                'Mutinerie du Sarkapwane',
                'Mutinerie kwapa',
                'Mystique des éléments',
                'Mémoire des éléments',
                'Nomade',
                'Onigovrille',
                'Ordonné',
                'Partage',
                'Pas de bras, pas de tracas',
                'Pas vu pas pris',
                'Premier',
                'Prudent',
                'Raser les murs',
                'Rien ne sert de se cacher',
                'Rinku meurtrier',
                'Sans-cœur',
                'Saïffe zaune',
                'Souffle du Printemps',
                'Statue',
                'Suis-moi je te fuis, fuis-moi je te suis',
                'Survivant',
                'Tambour Guignon',
                'Tel l\'esprit qui croyait prendre',
                'Tsume-grozu',
                'Tueur à gages',
                'Tueur à vue',
                'Tête dure',
                'Ultra instinct',
                'Un régime équilibré',
                'Un, deux, troix, BOZU !',
                'Versatile',
                'Voyage en terrain kouïu',
                'Zombie',
                'Ça va plonger chérie',
                'Économe',
                'Élitiste',
                'Élémentaire'
            ],
        ]);
    }

    /**
     * Store a newly created challenge in storage.
     */
    public function store(StoreChallengeRequest $request, Group $group, Season $season)
    {
        $this->authorize('view', [$season, $group]);

        $validated = $request->validated();

        // Créer le défi avec les champs failed_by
        $challenge = $season->challenges()->create([
            'name' => $validated['name'],
            'bet_amount' => $validated['bet_amount'],
            'failed_by_type' => $validated['failed_by']['type'] === 'user' ? User::class : GuestParticipant::class,
            'failed_by_id' => $validated['failed_by']['id']
        ]);

        // Attacher les participants
        foreach ($validated['participants'] as $participant) {
            if ($participant['type'] === 'user') {
                $challenge->participants()->attach($participant['id'], [
                    'participant_type' => User::class,
                    'participant_id' => $participant['id']
                ]);
            } else {
                $challenge->guestParticipants()->attach($participant['id'], [
                    'participant_type' => GuestParticipant::class,
                    'participant_id' => $participant['id']
                ]);
            }
        }

        return redirect()->route('seasons.show', [$group, $season])
            ->with('success', 'Le défi a été créé avec succès.');
    }

    public function markAsFailed(Request $request, Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('update', [$challenge, $season, $group]);

        $validated = $request->validate([
            'failed_by_type' => 'required|in:user,guest',
            'failed_by_id' => 'required',
        ]);

        if ($validated['failed_by_type'] === 'user') {
            $user = User::findOrFail($validated['failed_by_id']);
            $challenge->markAsFailedByUser($user);
        } else {
            $guest = GuestParticipant::findOrFail($validated['failed_by_id']);
            $challenge->markAsFailedByGuest($guest);
        }

        return redirect()->back()->with('success', 'Le défi a été marqué comme échoué.');
    }

    public function index(Group $group, Season $season, Request $request)
    {
        $this->authorize('view', [$season, $group]);

        // Charger les relations nécessaires pour le groupe
        $group->load(['members', 'guestMembers']);

        $challenges = $season->challenges()
            ->with([
                'participants' => function ($query) {
                    $query->select('users.id as user_id', 'users.name as user_name');
                },
                'guestParticipants' => function ($query) {
                    $query->select('guest_participants.id as guest_id', 'guest_participants.name as guest_name');
                },
                'failedBy'
            ])
            ->get()
            ->map(function ($challenge) {
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

        // Initialiser le classement avec tous les membres
        $ranking = [];

        // Ajouter les membres utilisateurs
        foreach ($group->members as $member) {
            $ranking[] = [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'user',
                'points' => 0,
                'participated_challenges' => 0,
                'failed_challenges' => 0
            ];
        }

        // Ajouter les membres invités
        foreach ($group->guestMembers as $member) {
            $ranking[] = [
                'id' => $member->id,
                'name' => $member->name,
                'type' => 'guest',
                'points' => 0,
                'participated_challenges' => 0,
                'failed_challenges' => 0
            ];
        }

        // Calculer les points pour chaque défi
        foreach ($challenges as $challenge) {
            $betAmount = $challenge['bet_amount'];
            $totalParticipants = count($challenge['participants']) + count($challenge['guest_participants']);

            // Points pour les participants
            foreach ($challenge['participants'] as $participant) {
                $memberIndex = array_search($participant['id'], array_column($ranking, 'id'));
                if ($memberIndex !== false) {
                    $ranking[$memberIndex]['points'] += $betAmount;
                    $ranking[$memberIndex]['participated_challenges']++;
                }
            }

            foreach ($challenge['guest_participants'] as $participant) {
                $memberIndex = array_search($participant['id'], array_column($ranking, 'id'));
                if ($memberIndex !== false) {
                    $ranking[$memberIndex]['points'] += $betAmount;
                    $ranking[$memberIndex]['participated_challenges']++;
                }
            }

            // Points négatifs pour celui qui a échoué
            if ($challenge['failed_by']) {
                $failedMemberIndex = array_search($challenge['failed_by']['id'], array_column($ranking, 'id'));
                if ($failedMemberIndex !== false) {
                    $penalty = $totalParticipants > 0 ? $betAmount * $totalParticipants : $betAmount;
                    $ranking[$failedMemberIndex]['points'] -= $penalty;
                    $ranking[$failedMemberIndex]['failed_challenges']++;
                }
            }
        }

        // Trier le classement par points
        usort($ranking, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        // Filtrer les défis si un membre est spécifié
        if ($request->has('member')) {
            $memberId = $request->member;
            $memberType = $request->member_type ?? 'user';

            if ($memberType === 'user') {
                $member = User::find($memberId);
            } else {
                $member = GuestParticipant::find($memberId);
            }

            if ($member) {
                $challenges = $challenges->filter(function ($challenge) use ($member) {
                    return in_array($member->id, array_column($challenge['participants'], 'id')) ||
                        in_array($member->id, array_column($challenge['guest_participants'], 'id')) ||
                        ($challenge['failed_by'] && $challenge['failed_by']['id'] == $member->id);
                });
            }
        }

        $user = Auth::user();
        $canManage = $group->canManage($user);

        return Inertia::render('Challenges/Index', [
            'group' => $group,
            'season' => $season,
            'challenges' => $challenges,
            'member' => $member ?? null,
            'ranking' => $ranking,
            'canManage' => $canManage
        ]);
    }

    public function destroy(Group $group, Season $season, Challenge $challenge)
    {
        $this->authorize('delete', [$challenge, $season, $group]);

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
