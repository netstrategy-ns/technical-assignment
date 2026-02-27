<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Hold;
use App\Services\AvailabilityService;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private QueueService $queueService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Event::with('category')->upcoming();

        // Apply filters
        $query->search($request->input('search'));
        $query->byCategory($request->input('category_id') ? (int) $request->input('category_id') : null);
        $query->byCity($request->input('city'));
        $query->byDateRange($request->input('date_from'), $request->input('date_to'));

        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Apply sorting
        $sort = $request->input('sort', 'nearest');
        $query = match ($sort) {
            'newest' => $query->orderByDesc('created_at'),
            'featured' => $query->orderByDesc('is_featured')->orderBy('starts_at'),
            default => $query->orderBy('starts_at'), // nearest
        };

        $events = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return Inertia::render('events/Index', [
            'events' => $events,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'category_id' => $request->input('category_id', ''),
                'city' => $request->input('city', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'featured' => $request->boolean('featured'),
                'sort' => $request->input('sort', 'nearest'),
            ],
        ]);
    }

    public function show(Request $request, Event $event): Response
    {
        $event->load('category');

        $userId = $request->user()?->id;

        $availability = $this->availabilityService->getEventAvailability($event->id, $userId);

        // Get user's active holds for this event
        $userHolds = collect();
        if ($userId) {
            $userHolds = Hold::with('ticketType')
                ->where('user_id', $userId)
                ->where('event_id', $event->id)
                ->active()
                ->get();
        }

        // Queue status
        $queueStatus = null;
        if ($event->queue_enabled && $userId) {
            $queueStatus = $this->queueService->getStatus($userId, $event);
        }

        // Calculate cart total
        $cartTotal = $userHolds->sum(fn (Hold $hold) => $hold->quantity * $hold->ticketType->price);

        return Inertia::render('events/Show', [
            'event' => $event,
            'availability' => $availability,
            'userHolds' => $userHolds,
            'cartTotal' => number_format($cartTotal, 2, '.', ''),
            'queueStatus' => $queueStatus,
            'saleStarted' => $event->isSaleStarted(),
        ]);
    }
}
