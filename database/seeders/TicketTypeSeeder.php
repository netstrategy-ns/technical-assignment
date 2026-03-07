<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Database\Support\EventGeneratorSupport;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Evitiamo errore Allowed memory size.
        Event::with('venueType', 'category')->chunk(50, function ($events) {
            foreach ($events as $event) {
            
                // Genera i nomi delle tipologie di biglietti per il tipo di venue
                $names = EventGeneratorSupport::ticketTypeNamesForVenueType($event->venueType->name);

                $venueName = $event->venueType->name;
                $categoryName = $event->category->name ?? null;

                if ($venueName === 'Stadio' && $categoryName === 'Concerti') {
                    $names = array_unique(array_merge($names, ['Prato']));
                }


                $picked = array_values(array_unique($names));
                if ($venueName !== 'Stadio') {
                    $picked = array_slice($picked, 0, min(3, count($picked)));
                }

                if ($picked === []) {
                    $picked = ['Standard'];
                }

                foreach ($picked as $name) {

                    // Evitiamo di creare tipologie di biglietti duplicati per lo stesso evento
                    TicketType::firstOrCreate(
                        [
                            'event_id' => $event->id,
                            'name' => $name,
                        ],
                        [
                            'venue_type_id' => $event->venue_type_id,
                        ]
                    );
                }
            }
        });
    }
}
