<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Hold;
use App\Models\OrderItem;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

class HoldService
{
    public const HOLD_DURATION_MINUTES = 10;

    /**
     * Create a hold for tickets. Uses SELECT FOR UPDATE to prevent overselling.
     *
     * @throws \RuntimeException
     */
    public function createHold(int $userId, int $ticketTypeId, int $quantity): Hold
    {
        return DB::transaction(function () use ($userId, $ticketTypeId, $quantity) {
            // Lock the ticket type row to serialize concurrent requests
            $ticketType = TicketType::where('id', $ticketTypeId)
                ->lockForUpdate()
                ->firstOrFail();

            $event = Event::findOrFail($ticketType->event_id);

            // Verify sale has started
            if (! $event->isSaleStarted()) {
                throw new \RuntimeException('Ticket sales have not started yet.');
            }

            // Count sold tickets
            $sold = (int) OrderItem::where('ticket_type_id', $ticketTypeId)->sum('quantity');

            // Count active holds (excluding current user)
            $othersHeld = (int) Hold::where('ticket_type_id', $ticketTypeId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->where('user_id', '!=', $userId)
                ->sum('quantity');

            // Current user's active holds for this ticket type
            $userHeld = (int) Hold::where('ticket_type_id', $ticketTypeId)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->sum('quantity');

            // Check availability
            $available = $ticketType->total_quantity - $sold - $othersHeld - $userHeld;
            if ($quantity > $available) {
                throw new \RuntimeException('Not enough tickets available.');
            }

            // Check per-user limit
            if ($userHeld + $quantity > $ticketType->per_user_limit) {
                throw new \RuntimeException(
                    "Per-user limit is {$ticketType->per_user_limit} tickets. You already hold {$userHeld}."
                );
            }

            return Hold::create([
                'user_id' => $userId,
                'ticket_type_id' => $ticketTypeId,
                'event_id' => $ticketType->event_id,
                'quantity' => $quantity,
                'expires_at' => now()->addMinutes(self::HOLD_DURATION_MINUTES),
                'status' => 'active',
            ]);
        });
    }

    /**
     * Release a hold (user-initiated cancel).
     */
    public function releaseHold(Hold $hold, int $userId): void
    {
        if ($hold->user_id !== $userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        if ($hold->status !== 'active') {
            throw new \RuntimeException('Hold is no longer active.');
        }

        $hold->update(['status' => 'expired']);
    }

    /**
     * Expire all holds past their expiry time.
     */
    public function expireHolds(): int
    {
        return Hold::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
