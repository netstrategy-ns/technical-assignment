<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Repositories\HoldRepository;
use App\Services\EventQueueService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HoldService
{
    public function __construct(
        private readonly HoldRepository $holds,
        private readonly EventQueueService $queue,
    ) {
    }

    public function createHold(int $userId, int $eventId, int $ticketTypeId, int $quantity): Hold
    {
        return DB::transaction(function () use ($userId, $eventId, $ticketTypeId, $quantity): Hold {
            $now = Carbon::now();

            $ticketType = TicketType::query()
                ->whereKey($ticketTypeId)
                ->with('event')
                ->lockForUpdate()
                ->firstOrFail();

            if ($ticketType->event_id !== $eventId) {
                throw new RuntimeException('Ticket type does not belong to event.');
            }

            $event = $ticketType->event;
            if (!$event) {
                throw new RuntimeException('Event not found.');
            }

            if ($event->sales_start_at->isAfter($now)) {
                throw new RuntimeException('Sales not started.');
            }

            $this->queue->assertAllowed($event, $userId);

            $soldCount = (int) Ticket::query()
                ->where('ticket_type_id', $ticketTypeId)
                ->count();

            $heldCount = $this->holds->activeSumForTicketType($ticketTypeId, $now);

            $available = max(0, (int) $ticketType->total_quantity - $soldCount - $heldCount);

            if ($quantity > $available) {
                throw new RuntimeException('Not enough tickets available.');
            }

            if (!is_null($ticketType->max_per_user)) {
                $userHeld = $this->holds->activeSumForUserTicketType($userId, $ticketTypeId, $now);
                if (($userHeld + $quantity) > $ticketType->max_per_user) {
                    throw new RuntimeException('Max tickets per user exceeded.');
                }
            }

            return $this->holds->create([
                'user_id' => $userId,
                'event_id' => $eventId,
                'ticket_type_id' => $ticketTypeId,
                'quantity' => $quantity,
                'expires_at' => $now->copy()->addMinutes(10),
                'status' => 'active',
            ]);
        });
    }
}
