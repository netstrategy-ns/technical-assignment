<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\IndexEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(IndexEventRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $return = $this->events->listEvents($filters);
        return response()->json($return);
    }

    public function featured(): JsonResponse
    {
        return response()->json($this->events->listFeatured());
    }

    public function show(Event $event): Response
    {
        $return = $this->events->loadAvailability($event);
        return Inertia::render('EventShow', [
            'event' => $return,
        ]);
    }
}
