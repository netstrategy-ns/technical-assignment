<?php

namespace App\Http\Controllers;

use App\Http\Requests\Queue\EnterQueueRequest;
use App\Http\Requests\Queue\StatusQueueRequest;
use App\Models\Event;
use App\Models\EventQueueEntry;
use App\Services\EventQueueService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class EventQueueController extends Controller
{
    public function __construct(
        private readonly EventQueueService $queue,
    ) {
    }

    public function enter(EnterQueueRequest $request): JsonResponse
    {
        $user = $request->user();
        $event = Event::query()->findOrFail((int) $request->input('event_id'));

        try {
            $entry = $this->queue->enter($event, $user->id);
            return response()->json($entry, 201);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function status(StatusQueueRequest $request): JsonResponse
    {
        $user = $request->user();
        $event = Event::query()->findOrFail((int) $request->input('event_id'));

        $entry = $this->queue->status($event, $user->id);

        if (!$entry) {
            return response()->json(['message' => 'Not in queue.'], 404);
        }

        return response()->json($entry);
    }
}
