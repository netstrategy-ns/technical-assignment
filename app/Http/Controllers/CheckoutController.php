<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Event;
use App\Models\Hold;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    public function show(Request $request, Event $event): Response|RedirectResponse
    {
        $userId = $request->user()->id;

        $holds = Hold::with('ticketType')
            ->where('user_id', $userId)
            ->where('event_id', $event->id)
            ->active()
            ->get();

        if ($holds->isEmpty()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'No active holds found. Your reservations may have expired.');
        }

        $total = $holds->sum(fn (Hold $hold) => $hold->quantity * $hold->ticketType->price);

        return Inertia::render('checkout/Show', [
            'event' => $event->load('category'),
            'holds' => $holds,
            'total' => number_format($total, 2, '.', ''),
        ]);
    }

    public function store(CheckoutRequest $request, Event $event): RedirectResponse
    {
        try {
            $order = $this->checkoutService->checkout(
                $request->user()->id,
                $event->id,
                $request->validated('idempotency_key'),
            );

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order confirmed! Your tickets have been purchased.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
