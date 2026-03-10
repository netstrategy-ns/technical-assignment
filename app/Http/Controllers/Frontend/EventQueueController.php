<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventShowResource;
use App\Models\Event;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EventQueueController extends Controller
{
    public function join(Request $request, int $eventId, QueueService $queueService): RedirectResponse
    {
        $event = Event::query()->findOrFail($eventId);

        if (! $event->isQueueEnabled()) {
            return back()->withErrors([
                'queue' => 'Questo evento non utilizza la coda.',
            ]);
        }

        $queueService->joinQueue($event, $request->user());

        return redirect()->route('events.show', ['event' => $event->slug]);
    }

    public function status(Request $request, int $eventId, QueueService $queueService): Response
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

        return Inertia::render('app/events/Show', [
            'event' => $eventResource->resolve($request),
            'saleNotStarted' => $event->isSaleNotStarted(),
            'queueStatus' => $status,
        ]);
    }
}
