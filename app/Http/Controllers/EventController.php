<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\IndexEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(IndexEventRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->validated();
        $return = $this->events->listEvents($filters);
        return response()->json($return);
    }

    public function featured(): JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        return response()->json($this->events->listFeatured());
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);
        $return = $this->events->loadAvailability($event);
        return response()->json($return);
    }
}
