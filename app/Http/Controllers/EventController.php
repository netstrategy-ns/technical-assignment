<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $categorySlug = $request->filled('category') ? $request->string('category')->toString() : null;
        $search = $request->filled('search') ? $request->string('search')->toString() : null;
        $searchForFilter = $search !== null ? trim($search) : null;
        $location = $request->filled('location') ? $request->string('location')->toString() : null;
        $locationForFilter = $location !== null ? trim($location) : null;
        $startDate = $request->filled('start_date') ? $request->string('start_date')->trim()->toString() : null;
        $endDate = $request->filled('end_date') ? $request->string('end_date')->trim()->toString() : null;
        $sort = $request->filled('sort') ? $request->string('sort')->toString() : 'date_asc';
        $sort = in_array($sort, ['date_asc', 'date_desc', 'featured_first'], true) ? $sort : 'date_asc';

        $query = Event::query()
            ->filterByActive()
            ->with(['category', 'venueType'])
            ->applySort($sort);

        if ($request->boolean('featured')) {
            $query->filterByFeatured();
        }

        if ($categorySlug) {
            $query->filterByCategory($categorySlug);
        }

        if ($searchForFilter !== null && $searchForFilter !== '') {
            $query->searchByTitle($searchForFilter);
        }

        if ($locationForFilter !== null && $locationForFilter !== '') {
            $query->filterByLocation($locationForFilter);
        }

        if ($startDate !== null && $endDate !== null) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('starts_at', [$start, $end]);
        } elseif ($startDate !== null) {
            $query->filterByStartDate($startDate);
        }

        $perPage = (int) $request->input('per_page', 24);
        $perPage = max(1, min(100, $perPage ?: 24));
        $events = $query->paginate($perPage)->withQueryString();

        $categories = EventCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $activeCategory = $categorySlug
            ? EventCategory::query()->where('slug', $categorySlug)->first()
            : null;

        return Inertia::render('frontend/events/Index', [
            'events' => $events,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
                'location' => $location,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'featured' => $request->boolean('featured'),
                'sort' => $sort,
            ],
            'activeCategory' => $activeCategory ? ['id' => $activeCategory->id, 'name' => $activeCategory->name, 'slug' => $activeCategory->slug] : null,
        ]);
    }

    /**
     * Dettaglio evento con tipologie, offerte (tickets), quote e disponibili per tipo.
     */
    public function show(Event $event): Response
    {
        $event->load([
            'category',
            'venueType',
            'ticketTypes.tickets',
            'ticketTypes.quota',
        ]);

        $ticketTypesWithAvailable = $event->ticketTypes->map(function ($ticketType) {
            return [
                'id' => $ticketType->id,
                'name' => $ticketType->name,
                'quota_quantity' => $ticketType->quota?->quantity ?? 0,
                'available_quantity' => $ticketType->getAvailableQuantity(),
                'tickets' => $ticketType->tickets->map(fn ($t) => [
                    'id' => $t->id,
                    'price' => $t->price,
                    'quantity_total' => $t->quantity_total,
                    'max_per_user' => $t->max_per_user,
                ]),
            ];
        });

        $saleStartsAt = $event->sale_starts_at;
        $saleNotStarted = $saleStartsAt && $saleStartsAt->isFuture();

        return Inertia::render('frontend/events/Show', [
            'event' => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'image_url' => $event->image_url,
                'starts_at' => $event->starts_at?->toIso8601String(),
                'ends_at' => $event->ends_at?->toIso8601String(),
                'sale_starts_at' => $event->sale_starts_at?->toIso8601String(),
                'category' => $event->category ? ['id' => $event->category->id, 'name' => $event->category->name] : null,
                'venueType' => $event->venueType ? ['id' => $event->venueType->id, 'name' => $event->venueType->name] : null,
                'ticket_types' => $ticketTypesWithAvailable,
            ],
            'saleNotStarted' => $saleNotStarted,
        ]);
    }
}
