<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use App\Models\GuestParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeParticipantFactory extends Factory
{
    public function definition(): array
    {
        // Par défaut, on utilise un utilisateur
        return [
            'challenge_id' => Challenge::factory(),
            'participant_type' => User::class,
            'participant_id' => User::factory(),
        ];
    }

    public function withUser(User $user = null): self
    {
        return $this->state(function (array $attributes) use ($user) {
            $user = $user ?? User::factory();
            return [
                'participant_type' => User::class,
                'participant_id' => $user->id,
            ];
        });
    }

    public function withGuest(GuestParticipant $guest = null): self
    {
        return $this->state(function (array $attributes) use ($guest) {
            $guest = $guest ?? GuestParticipant::factory();
            return [
                'participant_type' => GuestParticipant::class,
                'participant_id' => $guest->id,
            ];
        });
    }
}
