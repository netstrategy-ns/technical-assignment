<?php

namespace App\Repositories;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class EventRepository
{
    public function all(array $filters = []): Collection
    {
        $query = Event::query();

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function featured(int $limit = 10): Collection
    {
        return Event::query()
            ->where('is_featured', true)
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    public function loadWithAvailability(Event $event): Event
    {
        $now = Carbon::now();

        $event->load([
            'ticketTypes' => function ($query) use ($now): void {
                $query->withCount([
                    'tickets as sold_count' => function (Builder $ticketQuery): void {
                        $ticketQuery->where('status', 'valid');
                    },
                ])
                    ->withSum([
                        'holds as hold_count' => function (Builder $holdQuery) use ($now): void {
                            $holdQuery->where('status', 'active')
                                ->where('expires_at', '>', $now);
                        },
                    ], 'quantity');
            },
        ]);

        return $event;
    }

    /**
     * @param array{
     *   search?: string,
     *   category?: string,
     *   city?: string,
     *   featured?: bool,
     *   date_from?: string,
     *   date_to?: string,
     *   order?: string
     * } $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        if (array_key_exists('featured', $filters)) {
            $query->where('is_featured', (bool) $filters['featured']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('starts_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('starts_at', '<=', $filters['date_to']);
        }

        $order = $filters['order'] ?? 'starts_at_asc';

        match ($order) {
            'starts_at_desc' => $query->orderByDesc('starts_at'),
            'created_at_desc' => $query->orderByDesc('created_at'),
            'featured_first' => $query->orderByDesc('is_featured')->orderBy('starts_at'),
            default => $query->orderBy('starts_at'),
        };
    }
}
