<?php

namespace App\Services;

use App\Models\EventQueue;
use App\Models\Hold;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Process checkout — converts active holds into a confirmed order.
     * Idempotent: if an order with the same idempotency_key exists, returns it.
     *
     * @throws \RuntimeException
     */
    public function checkout(int $userId, int $eventId, string $idempotencyKey): Order
    {
        return DB::transaction(function () use ($userId, $eventId, $idempotencyKey) {
            // Idempotency check: return existing order if same key
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder) {
                return $existingOrder;
            }

            // Lock and fetch user's active holds for this event
            $holds = Hold::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->get();

            if ($holds->isEmpty()) {
                throw new \RuntimeException('No active holds found. They may have expired.');
            }

            // Verify all holds are still valid (double-check after lock)
            foreach ($holds as $hold) {
                if (! $hold->isActive()) {
                    throw new \RuntimeException('Some holds have expired. Please try again.');
                }
            }

            // Calculate total
            $totalAmount = 0;
            $holdsByTicketType = $holds->groupBy('ticket_type_id');

            foreach ($holdsByTicketType as $ticketTypeId => $typeHolds) {
                $ticketType = $typeHolds->first()->ticketType;
                $quantity = $typeHolds->sum('quantity');
                $totalAmount += $quantity * $ticketType->price;
            }

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'event_id' => $eventId,
                'idempotency_key' => $idempotencyKey,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
            ]);

            // Create order items
            foreach ($holdsByTicketType as $ticketTypeId => $typeHolds) {
                $ticketType = $typeHolds->first()->ticketType;
                $quantity = $typeHolds->sum('quantity');

                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketTypeId,
                    'quantity' => $quantity,
                    'unit_price' => $ticketType->price,
                ]);
            }

            // Mark holds as converted
            Hold::whereIn('id', $holds->pluck('id'))
                ->update(['status' => 'converted']);

            // Mark queue entry as completed if applicable
            EventQueue::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->update(['status' => 'completed']);

            return $order;
        });
    }
}
