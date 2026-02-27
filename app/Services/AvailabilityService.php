<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\OrderItem;
use App\Models\TicketType;

class AvailabilityService
{
    /**
     * Get availability for all ticket types of an event.
     *
     * @return array<int, array{ticket_type_id: int, name: string, price: string, total_quantity: int, per_user_limit: int, sold: int, held: int, available: int}>
     */
    public function getEventAvailability(int $eventId, ?int $userId = null): array
    {
        $ticketTypes = TicketType::where('event_id', $eventId)
            ->orderBy('sort_order')
            ->get();

        $result = [];

        foreach ($ticketTypes as $ticketType) {
            $sold = OrderItem::where('ticket_type_id', $ticketType->id)->sum('quantity');

            $held = Hold::where('ticket_type_id', $ticketType->id)
                ->active()
                ->sum('quantity');

            $available = max(0, $ticketType->total_quantity - $sold - $held);

            $userHeld = 0;
            if ($userId) {
                $userHeld = Hold::where('ticket_type_id', $ticketType->id)
                    ->where('user_id', $userId)
                    ->active()
                    ->sum('quantity');
            }

            $result[] = [
                'ticket_type_id' => $ticketType->id,
                'name' => $ticketType->name,
                'price' => $ticketType->price,
                'total_quantity' => $ticketType->total_quantity,
                'per_user_limit' => $ticketType->per_user_limit,
                'sold' => (int) $sold,
                'held' => (int) $held,
                'available' => $available,
                'user_held' => $userHeld,
            ];
        }

        return $result;
    }
}
