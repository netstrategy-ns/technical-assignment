<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::factory()
            ->count(15)
            ->create()
            ->each(function (Event $event): void {
                TicketType::factory()
                    ->count(rand(2, 4))
                    ->create(['event_id' => $event->id]);
            });

        $events->random(8)->each(function (Event $event): void {
            $event->update([
                'sales_start_at' => now()->subDays(rand(1, 20)),
            ]);
        });

        $events->random(4)->each(function (Event $event): void {
            $event->update([
                'queue_enabled' => true,
                'queue_max_concurrent' => rand(50, 150),
            ]);
        });
    }
}
