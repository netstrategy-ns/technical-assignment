<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\VenueType;
use Database\Support\EventGeneratorSupport;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{ 
    /**
    * Run the database seeds.
    */
    public function run(): void
    {
        $categories = EventCategory::all();
        $venueTypes = VenueType::all();

        
        if ($categories->isEmpty() || $venueTypes->isEmpty()) {
            return;
        }

        $count = 50;
        $now = now();

        for ($i = 0; $i < $count; $i++) {

            // Basiamo data fine evento e inizio prevendita su starts_at
            $startsAt = $now->copy()->addWeeks($i + 1)->addDays(rand(0, 14));

            $endsAt = (clone $startsAt)->addHours(rand(1, 4));
           
            $saleStartsAt = (clone $startsAt)->subDays(rand(7, 60));

            // Se primo evento crea evento con prevendita aperta
            if ($i === 0) {
                $saleStartsAt = $now->copy()->subDays(rand(1, 30));
            }

            // Se ultimo evento crea evento con prevendita in data futura
            if ($i === $count - 1) {
                $saleStartsAt = $now->copy()->addDays(rand(1, 20));
            }

            $category = $categories->random();
            $venueType = $venueTypes->random();
            $year = (int) $startsAt->format('Y');

            // Generiamo dei dati utilizzando il file di supporto support/EventGeneratorSupport.php
            $generated = EventGeneratorSupport::generateWithCity($category, $venueType, $year);
            $title = $generated['title'];
            $city = $generated['city'];
            $location = EventGeneratorSupport::getLocation($venueType, $city);

            // Numero di biglietti disponibili per l'evento
            $availableTickets = in_array($venueType->name, ['Stadio', 'Arena'], true)
                ? random_int(15_000, 75_000)
                : random_int(500, 5_000);

            $isQueueEnabled = $i === 0;
            $queueConfig = $isQueueEnabled ? [
                'max_concurrent' => random_int(1, 3),
                'duration_minutes' => 10,
            ] : null;

            Event::factory()->create([
                'title' => $title,
                'description' => fake()->paragraph(6),
                'location' => $location,
                'event_category_id' => $category->id,
                'venue_type_id' => $venueType->id,
                'is_featured' => $i < 2,
                'is_active' => true,
                'queue_enabled' => $isQueueEnabled,
                'queue_config' => $queueConfig,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'sale_starts_at' => $saleStartsAt,
                'available_tickets' => $availableTickets,
                'image_url' => 'https://placehold.co/640x480?text=' . rawurlencode($title),
            ]);
        }
    }
}
