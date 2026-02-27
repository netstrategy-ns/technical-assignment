<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository
{

    public function forUserWithDetails(int $userId): Collection
    {
        return Ticket::query()
            ->where('user_id', $userId)
            ->with(['event', 'ticketType', 'order'])
            ->orderByDesc('purchased_at')
            ->get();
    }
}
