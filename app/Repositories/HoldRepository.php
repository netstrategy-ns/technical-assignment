<?php

namespace App\Repositories;

use App\Models\Hold;
use Carbon\Carbon;

class HoldRepository
{
    public function activeSumForTicketType(int $ticketTypeId, Carbon $now): int
    {
        return (int) Hold::query()
            ->where('ticket_type_id', $ticketTypeId)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->sum('quantity');
    }

    public function activeSumForUserTicketType(int $userId, int $ticketTypeId, Carbon $now): int
    {
        return (int) Hold::query()
            ->where('user_id', $userId)
            ->where('ticket_type_id', $ticketTypeId)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->sum('quantity');
    }

    /**
     * @param array{
     *   user_id: int,
     *   event_id: int,
     *   ticket_type_id: int,
     *   quantity: int,
     *   expires_at: \DateTimeInterface,
     *   status: string
     * } $data
     */
    public function create(array $data): Hold
    {
        return Hold::query()->create($data);
    }

    public function activeForUserEvent(int $userId, int $eventId, Carbon $now)
    {
        return Hold::query()
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->lockForUpdate()
            ->get();
    }

    public function activeForUser(int $userId, Carbon $now)
    {
        return Hold::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('expires_at', '>', $now)
            ->with(['event', 'ticketType'])
            ->orderBy('expires_at')
            ->get();
    }
}
