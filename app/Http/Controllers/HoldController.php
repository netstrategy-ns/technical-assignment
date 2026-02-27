<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHoldRequest;
use App\Models\Event;
use App\Models\Hold;
use App\Services\HoldService;
use Illuminate\Http\RedirectResponse;

class HoldController extends Controller
{
    public function __construct(
        private HoldService $holdService,
    ) {}

    public function store(StoreHoldRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->holdService->createHold(
                $request->user()->id,
                $request->validated('ticket_type_id'),
                $request->validated('quantity'),
            );

            return back()->with('success', 'Tickets reserved! You have 10 minutes to complete checkout.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Hold $hold): RedirectResponse
    {
        try {
            $this->holdService->releaseHold($hold, request()->user()->id);

            return back()->with('success', 'Hold released.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
