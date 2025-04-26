<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'owner_id' => \App\Models\User::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Group $group) {
            // Ajouter le propriétaire comme membre
            $group->members()->attach($group->owner_id, ['role' => 'owner']);

            // Ajouter quelques membres aléatoires
            $users = \App\Models\User::inRandomOrder()->take(rand(2, 5))->get();
            foreach ($users as $user) {
                if ($user->id !== $group->owner_id) {
                    $group->members()->attach($user->id, [
                        'role' => fake()->randomElement(['moderator', 'member'])
                    ]);
                }
            }
        });
    }
}
