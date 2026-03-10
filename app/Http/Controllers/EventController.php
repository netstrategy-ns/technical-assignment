<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Http\Requests\Events\EventIndexRequest;
use App\Http\Resources\Events\EventShowResource;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(EventIndexRequest $request): Response
    {
        $categories = EventCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $filters = $request->filters();

        $events = Event::query()
            ->filterByActive()
            ->with('category:id,name,slug')
            ->applyFilters($filters)
            ->applySort($filters['sort'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        $activeCategory = null;

        if ($filters['category'] !== null) {
            $activeCategory = $categories
                ->firstWhere('slug', $filters['category'])?->only(['id', 'name', 'slug']);
        }

        return Inertia::render('app/events/Index', [
            'events' => $events,
            'categories' => $categories,
            'filters' => $filters,
            'activeCategory' => $activeCategory,
        ]);
    }

    /**
     * Dettaglio evento con tipologie, offerte (tickets), quote e disponibili per tipo.
     * Si passa l'array risolto della Resource così il frontend riceve event.* direttamente
     * (senza wrapper "data" di JsonResource).
     */
    public function show(Event $event, Request $request, QueueService $queueService): Response
    {
        $event->load([
            'category',
            'venueType',
            'ticketTypes.tickets',
            'ticketTypes.quota',
        ]);
        $resource = new EventShowResource($event);
        $queueStatus = $request->user() === null ? null : $queueService->getQueueStatus($request->user(), $event);
        return Inertia::render('app/events/Show', [
            'event' => $resource->resolve(request()),
            'saleNotStarted' => $event->isSaleNotStarted(),
            'queueStatus' => $queueStatus,
        ]);
    }

}
