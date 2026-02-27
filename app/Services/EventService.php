<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    public function __construct(
        private readonly EventRepository $events,
    ) {
    }

    public function listEvents(array $filters = []): Collection
    {
        return $this->events->all($filters);
    }

    public function listFeatured(int $limit = 10): Collection
    {
        return $this->events->featured($limit);
    }

    public function loadAvailability(Event $event): Event
    {
        $event = $this->events->loadWithAvailability($event);

        $event->ticketTypes->transform(function ($ticketType) {
            $sold = (int) ($ticketType->sold_count ?? 0);
            $held = (int) ($ticketType->hold_count ?? 0);
            $available = max(0, (int) $ticketType->total_quantity - $sold - $held);

            $ticketType->available_quantity = $available;

            return $ticketType;
        });

        return $event;
    }
}
