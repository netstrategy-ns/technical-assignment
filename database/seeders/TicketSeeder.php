<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Evitiamo errore Allowed memory size.
        TicketType::chunk(50, function ($ticketTypes) {
            foreach ($ticketTypes as $ticketType) {

                // Evitiamo duplicati per tipologia di biglietto.
                if ($ticketType->tickets()->exists()) {
                    continue;
                }

                Ticket::factory()->create([
                    'ticket_type_id' => $ticketType->id,
                    'price' => fake()->randomFloat(2, 10, 100),
                    'max_per_user' => fake()->numberBetween(1, 10),
                ]);

            }
        });
    }
}
