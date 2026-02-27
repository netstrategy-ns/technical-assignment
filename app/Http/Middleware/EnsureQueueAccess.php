<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Services\QueueService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureQueueAccess
{
    public function __construct(
        private QueueService $queueService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event');

        if (! $event instanceof Event) {
            return $next($request);
        }

        if (! $event->queue_enabled) {
            return $next($request);
        }

        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $this->queueService->hasAccess($request->user()->id, $event)) {
            return back()->with('error', 'You need queue access to perform this action. Please wait for your turn.');
        }

        return $next($request);
    }
}
