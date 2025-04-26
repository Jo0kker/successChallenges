<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Season;
use App\Models\Challenge;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création de l'utilisateur test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Création de 5 utilisateurs supplémentaires
        User::factory(5)->create();

        // Création de 3 groupes
        Group::factory(3)->create();

        // Pour chaque groupe, créer 2 saisons
        Group::all()->each(function ($group) {
            Season::factory(2)->create([
                'group_id' => $group->id
            ]);
        });

        // Pour chaque saison, créer 5 défis
        Season::all()->each(function ($season) {
            Challenge::factory(5)->create([
                'season_id' => $season->id
            ]);
        });
    }
}
