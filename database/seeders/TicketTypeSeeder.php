<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $ticketConfigs = [
            'rock-night-2026' => [
                ['name' => 'Standard', 'price' => 45.00, 'total_quantity' => 200, 'per_user_limit' => 4],
                ['name' => 'VIP', 'price' => 120.00, 'total_quantity' => 50, 'per_user_limit' => 2],
                ['name' => 'Backstage Pass', 'price' => 350.00, 'total_quantity' => 10, 'per_user_limit' => 2],
            ],
            'la-traviata' => [
                ['name' => 'Platea', 'price' => 80.00, 'total_quantity' => 150, 'per_user_limit' => 4],
                ['name' => 'Palco', 'price' => 200.00, 'total_quantity' => 30, 'per_user_limit' => 2],
            ],
            'opera-under-the-stars' => [
                ['name' => 'Standard', 'price' => 55.00, 'total_quantity' => 300, 'per_user_limit' => 4],
                ['name' => 'Premium', 'price' => 150.00, 'total_quantity' => 80, 'per_user_limit' => 2],
            ],
            'champions-league-final' => [
                ['name' => 'Category 3', 'price' => 70.00, 'total_quantity' => 100, 'per_user_limit' => 4],
                ['name' => 'Category 2', 'price' => 150.00, 'total_quantity' => 60, 'per_user_limit' => 2],
                ['name' => 'Category 1', 'price' => 300.00, 'total_quantity' => 20, 'per_user_limit' => 2],
            ],
            'summer-beats-festival' => [
                ['name' => 'Day Pass', 'price' => 40.00, 'total_quantity' => 500, 'per_user_limit' => 4],
                ['name' => '3-Day Pass', 'price' => 100.00, 'total_quantity' => 200, 'per_user_limit' => 4],
                ['name' => 'VIP 3-Day', 'price' => 250.00, 'total_quantity' => 50, 'per_user_limit' => 2],
            ],
            'milan-derby' => [
                ['name' => 'Curva', 'price' => 35.00, 'total_quantity' => 100, 'per_user_limit' => 4],
                ['name' => 'Tribuna', 'price' => 90.00, 'total_quantity' => 60, 'per_user_limit' => 2],
            ],
            'new-year-gala-2027' => [
                ['name' => 'Standard', 'price' => 60.00, 'total_quantity' => 200, 'per_user_limit' => 4],
                ['name' => 'VIP Table', 'price' => 200.00, 'total_quantity' => 30, 'per_user_limit' => 2],
            ],
            'ai-summit-italy-2027' => [
                ['name' => 'General Admission', 'price' => 50.00, 'total_quantity' => 300, 'per_user_limit' => 4],
                ['name' => 'Workshop Pass', 'price' => 120.00, 'total_quantity' => 80, 'per_user_limit' => 2],
            ],
            'tech-summit-2026' => [
                ['name' => 'Standard', 'price' => 35.00, 'total_quantity' => 150, 'per_user_limit' => 4],
                ['name' => 'Premium', 'price' => 80.00, 'total_quantity' => 40, 'per_user_limit' => 2],
            ],
            'stand-up-saturday' => [
                ['name' => 'Standard', 'price' => 25.00, 'total_quantity' => 120, 'per_user_limit' => 4],
                ['name' => 'Front Row', 'price' => 50.00, 'total_quantity' => 20, 'per_user_limit' => 2],
            ],
            'jazz-night-rome' => [
                ['name' => 'Standard', 'price' => 30.00, 'total_quantity' => 100, 'per_user_limit' => 4],
                ['name' => 'VIP Lounge', 'price' => 75.00, 'total_quantity' => 20, 'per_user_limit' => 2],
            ],
            'exclusive-vip-night' => [
                ['name' => 'Standard', 'price' => 150.00, 'total_quantity' => 5, 'per_user_limit' => 2],
                ['name' => 'VIP', 'price' => 500.00, 'total_quantity' => 3, 'per_user_limit' => 1],
            ],
        ];

        foreach ($ticketConfigs as $eventSlug => $types) {
            $event = Event::where('slug', $eventSlug)->first();

            if (! $event) {
                continue;
            }

            foreach ($types as $sortOrder => $typeData) {
                TicketType::create([
                    'event_id' => $event->id,
                    'name' => $typeData['name'],
                    'price' => $typeData['price'],
                    'total_quantity' => $typeData['total_quantity'],
                    'per_user_limit' => $typeData['per_user_limit'],
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }
}
