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
        Event::factory()
            ->count(15)
            ->create()
            ->each(function (Event $event): void {
                TicketType::factory()
                    ->count(rand(2, 4))
                    ->create(['event_id' => $event->id]);
            });
    }
}
