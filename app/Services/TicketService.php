<?php

namespace App\Services;

use App\Repositories\TicketRepository;
use Illuminate\Database\Eloquent\Collection;

class TicketService
{
    public function __construct(
        private readonly TicketRepository $tickets,
    ) {}

    public function listForUserWithDetails(int $userId): Collection
    {
        return $this->tickets->forUserWithDetails($userId);
    }
}
