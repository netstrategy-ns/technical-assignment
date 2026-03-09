<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'status' => $order->status->value,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at?->toIso8601String(),
            ]);

        return Inertia::render('frontend/orders/Index', [
            'orders' => $orders,
            'totalOrders' => $orders->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load('orderItems.ticket.ticketType.event');

        return Inertia::render('frontend/orders/Show', [
            'order' => $order->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
