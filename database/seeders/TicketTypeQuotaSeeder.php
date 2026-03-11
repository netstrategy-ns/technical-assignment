<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketTypeQuota;
use Illuminate\Database\Seeder;

class TicketTypeQuotaSeeder extends Seeder
{
    /**
     * Assegna a ogni TicketType dell'evento una quota (quantity) in modo che la somma
     * delle quote non superi event.available_tickets (regola in TicketTypeQuotaObserver).
     */
    public function run(): void
    {
        Event::query()
            ->where('available_tickets', '>', 0)
            ->with('ticketTypes')
            ->chunk(50, function ($events): void {
                foreach ($events as $event) {
                    $ticketTypes = $event->ticketTypes;
                    if ($ticketTypes->isEmpty()) {
                        continue;
                    }

                    $totalAllowed = (int) $event->available_tickets;
                    $count = $ticketTypes->count();
                    $perType = (int) floor($totalAllowed / $count);
                    $remainder = $totalAllowed - ($perType * $count);

                    foreach ($ticketTypes as $index => $ticketType) {
                        $quantity = $perType + ($index < $remainder ? 1 : 0);
                        if ($quantity <= 0) {
                            continue;
                        }

                        TicketTypeQuota::updateOrCreate(
                            ['ticket_type_id' => $ticketType->id],
                            ['quantity' => $quantity]
                        );
                    }
                }
            });
    }
}
