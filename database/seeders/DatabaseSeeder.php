<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Primary test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Additional users for testing
        User::factory(10)->create();

        $this->call([
            CategorySeeder::class,
            EventSeeder::class,
            TicketTypeSeeder::class,
        ]);
    }
}
