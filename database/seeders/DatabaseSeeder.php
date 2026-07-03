<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $agency = Agency::factory()->create([
            'name' => 'AgentFlow Demo Agency',
            'legal_name' => 'AgentFlow Demo Agency SARL',
            'email' => 'agency@example.com',
            'city' => 'Casablanca',
            'country' => 'MA',
        ]);

        User::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
