<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Season;
use App\Models\User;
use App\Models\GuestParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Créer un utilisateur qui échouera au challenge
        $failedByUser = User::factory()->create();

        return [
            'name' => fake()->word(),
            'bet_amount' => fake()->numberBetween(100, 5000),
            'season_id' => Season::factory(),
            'failed_by_type' => User::class,
            'failed_by_id' => $failedByUser->id,
        ];
    }

    public function failedByUser(User $user = null): self
    {
        return $this->state(function (array $attributes) use ($user) {
            $user = $user ?? User::factory();
            return [
                'failed_by_type' => User::class,
                'failed_by_id' => $user->id,
            ];
        });
    }

    public function failedByGuest(GuestParticipant $guest = null): self
    {
        return $this->state(function (array $attributes) use ($guest) {
            $guest = $guest ?? GuestParticipant::factory();
            return [
                'failed_by_type' => GuestParticipant::class,
                'failed_by_id' => $guest->id,
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Challenge $challenge) {
            $season = $challenge->season;
            $group = $season->group;

            // Créer quelques utilisateurs si nécessaire
            $users = User::count() < 3 ? User::factory(3)->create() : User::all();

            // Ajouter les utilisateurs au groupe s'ils n'y sont pas déjà
            foreach ($users as $user) {
                if (!$group->members()->where('member_id', $user->id)->exists()) {
                    $group->members()->attach($user->id, [
                        'role' => fake()->randomElement(['owner', 'moderator', 'member']),
                        'member_type' => User::class
                    ]);
                }
            }

            // Sélectionner un membre aléatoire comme celui qui a échoué
            $allMembers = $group->allMembers();
            $failedBy = $allMembers->random();

            if ($failedBy) {
                $challenge->update([
                    'failed_by_type' => $failedBy['type'] === 'guest' ? GuestParticipant::class : User::class,
                    'failed_by_id' => $failedBy['id']
                ]);

                // Ajouter tous les autres membres comme participants
                $participants = [];
                foreach ($allMembers as $member) {
                    if ($member['id'] !== $failedBy['id']) {
                        $participants[] = [
                            'participant_type' => $member['type'] === 'guest' ? GuestParticipant::class : User::class,
                            'participant_id' => $member['id']
                        ];
                    }
                }

                // Synchroniser les participants
                DB::table('challenge_participants')->insert(
                    array_map(fn($p) => array_merge($p, [
                        'challenge_id' => $challenge->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]), $participants)
                );
            }
        });
    }
}
