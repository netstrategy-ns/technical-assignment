<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Event;
use App\Repositories\HoldRepository;
use App\Services\EventQueueService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private readonly HoldRepository $holds,
        private readonly EventQueueService $queue,
    ) {}

    public function checkout(int $userId, int $eventId, ?string $idempotencyKey = null): Order
    {
        if ($idempotencyKey) {
            $existing = Order::query()
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        try {
            return DB::transaction(function () use ($userId, $eventId, $idempotencyKey): Order {
                $now = Carbon::now();

                $event = Event::query()->find($eventId);
                if ($event) {
                    $this->queue->assertAllowed($event, $userId);
                }

                $holds = $this->holds->activeForUserEvent($userId, $eventId, $now);

                if ($holds->isEmpty()) {
                    throw new RuntimeException('No active holds for this event.');
                }

                $ticketTypeIds = $holds->pluck('ticket_type_id')->unique()->values()->all();

                $ticketTypes = TicketType::query()
                    ->whereIn('id', $ticketTypeIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $totalAmount = 0;

                foreach ($holds as $hold) {
                    $ticketType = $ticketTypes->get($hold->ticket_type_id);
                    if (!$ticketType) {
                        throw new RuntimeException('Ticket type not found.');
                    }

                    $totalAmount += $ticketType->price * $hold->quantity;
                }

                $order = Order::query()->create([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'confirmed',
                    'total_amount' => $totalAmount,
                    'currency' => 'EUR',
                ]);

                foreach ($holds as $hold) {
                    $ticketType = $ticketTypes->get($hold->ticket_type_id);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketType->id,
                        'quantity' => $hold->quantity,
                        'unit_price' => $ticketType->price,
                        'total_price' => $ticketType->price * $hold->quantity,
                    ]);

                    for ($i = 0; $i < $hold->quantity; $i++) {
                        Ticket::query()->create([
                            'order_id' => $order->id,
                            'user_id' => $userId,
                            'event_id' => $eventId,
                            'ticket_type_id' => $ticketType->id,
                            'status' => 'valid',
                            'purchased_at' => $now,
                        ]);
                    }
                }

                $holds->each(function ($hold) use ($now): void {
                    $hold->status = 'consumed';
                    $hold->expires_at = $now;
                    $hold->save();
                });

                if ($event) {
                    $this->queue->markCompleted($event, $userId);
                }

                return $order;
            });
        } catch (QueryException $exception) {
            if (!$idempotencyKey) {
                throw $exception;
            }

            $existing = Order::query()
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }
}
