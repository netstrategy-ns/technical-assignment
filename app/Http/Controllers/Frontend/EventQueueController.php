<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventShowResource;
use App\Models\Event;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventQueueController extends Controller
{
    public function join(Request $request, int $eventId, QueueService $queueService): JsonResponse
    {
        $event = Event::query()->findOrFail($eventId);

        if (! $event->isQueueEnabled()) {
            return response()->json(['message' => 'Questo evento non utilizza la coda.'], 409);
        }

        $entry = $queueService->joinQueue($event, $request->user());
        $status = $queueService->getQueueStatus($request->user(), $event);

        return response()->json([
            'queue_status' => $status,
            'entry' => [
                'id' => $entry->id,
                'status' => $entry->status->value,
            ],
        ]);
    }

    public function status(Request $request, int $eventId, QueueService $queueService): JsonResponse
    {
        $event = Event::query()
            ->with([
                'category:id,name',
                'venueType:id,name',
                'ticketTypes.tickets',
                'ticketTypes.quota',
            ])
            ->findOrFail($eventId);
        $status = $queueService->getQueueStatus($request->user(), $event);
        $eventResource = new EventShowResource($event);

        return response()->json([
            'queue_status' => $status,
            'event' => $eventResource->resolve($request),
        ]);
    }
}
