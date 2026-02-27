<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(
        private QueueService $queueService,
    ) {}

    public function store(Request $request, Event $event): RedirectResponse
    {
        try {
            $this->queueService->joinQueue($request->user()->id, $event);

            return back()->with('info', 'You have joined the queue. Please wait for your turn.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $status = $this->queueService->getStatus($request->user()->id, $event);

        return response()->json($status);
    }
}
