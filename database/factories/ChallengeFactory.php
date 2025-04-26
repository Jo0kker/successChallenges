<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'season_id' => Season::factory(),
            'name' => $this->faker->word,
            'bet_amount' => $this->faker->numberBetween(100, 10000),
            'failed_by' => null,
        ];
    }

    public function failed(User $user = null): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'failed_by' => $user?->id ?? User::factory(),
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Challenge $challenge) {
            $season = $challenge->season;
            $group = $season->group;
            $members = $group->members()->get();

            // Sélectionner un membre aléatoire comme celui qui a échoué
            $failedBy = $members->random();
            $challenge->update(['failed_by' => $failedBy->id]);

            // Ajouter tous les autres membres comme participants
            foreach ($members as $member) {
                if ($member->id !== $failedBy->id) {
                    $challenge->participants()->attach($member->id);
                }
            }
        });
    }
}
