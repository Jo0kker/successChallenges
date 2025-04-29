<?php

namespace Database\Factories;

use App\Models\User;
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
        $owner = User::factory()->create();

        return [
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'owner_id' => $owner->id,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterCreating(function ($group) {
            $group->members()->attach($group->owner_id, [
                'role' => 'owner',
                'member_type' => User::class
            ]);
        });
    }
}
