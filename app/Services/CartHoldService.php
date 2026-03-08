<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartHoldService
{
    public const HOLD_MINUTES = 10;

    public function buildCartPayload(User $user): array
    {
        $holds = Hold::query()
            ->with(['ticket.ticketType.event'])
            ->whereBelongsTo($user)
            ->active()
            ->valid()
            ->orderBy('expires_at')
            ->get();

        $items = $holds
            ->map(fn (Hold $hold): array => $this->mapHold($hold))
            ->values()
            ->all();

        $totalItems = array_sum(array_column($items, 'quantity'));
        $totalAmount = array_reduce(
            $items,
            fn (float $carry, array $item): float => $carry + ((float) $item['ticket']['price'] * $item['quantity']),
            0.0,
        );

        return [
            'items' => $items,
            'summary' => [
                'total_items' => $totalItems,
                'total_amount' => round($totalAmount, 2),
            ],
        ];
    }

    public function placeHold(User $user, int $ticketId, int $quantity): Hold
    {
        return DB::transaction(function () use ($user, $ticketId, $quantity): Hold {
            $ticket = Ticket::query()
                ->with(['ticketType.event'])
                ->lockForUpdate()
                ->find($ticketId);

            if ($ticket === null) {
                throw (new ModelNotFoundException())->setModel(Ticket::class, [$ticketId]);
            }

            $event = $ticket->ticketType?->event;

            if ($event === null) {
                throw ValidationException::withMessages([
                    'ticket_id' => 'Impossibile associare il biglietto a un evento valido.',
                ]);
            }

            if ($event->isSaleNotStarted()) {
                throw ValidationException::withMessages([
                    'ticket_id' => 'La vendita per questo evento non e ancora iniziata.',
                ]);
            }

            $existingHold = Hold::query()
                ->whereBelongsTo($user)
                ->where('ticket_id', $ticket->id)
                ->where('status', '!=', HoldStatusEnum::COMPLETED->value)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existingHold !== null) {
                Hold::query()
                    ->whereBelongsTo($user)
                    ->where('ticket_id', $ticket->id)
                    ->whereKeyNot($existingHold->id)
                    ->where('status', '!=', HoldStatusEnum::COMPLETED->value)
                    ->update([
                        'status' => HoldStatusEnum::EXPIRED->value,
                        'expires_at' => now(),
                    ]);
            }

            $currentQuantity = $existingHold?->isValid() ? $existingHold->quantity : 0;
            $targetQuantity = $currentQuantity + max(1, $quantity);

            if ($ticket->max_per_user !== null && $targetQuantity > $ticket->max_per_user) {
                throw ValidationException::withMessages([
                    'quantity' => 'Hai superato il limite massimo acquistabile per questo biglietto.',
                ]);
            }

            $availableQuantity = $ticket->getAvailableQuantity($existingHold?->id);

            if ($targetQuantity > $availableQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'La quantita richiesta non e piu disponibile.',
                ]);
            }

            $hold = $existingHold ?? new Hold([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
            ]);

            $hold->fill([
                'quantity' => $targetQuantity,
                'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
                'status' => HoldStatusEnum::ACTIVE,
            ])->save();

            return $hold->fresh(['ticket.ticketType.event']);
        }, 3);
    }

    public function releaseHold(User $user, Hold $hold): Hold
    {
        if ($hold->user_id !== $user->id) {
            throw (new ModelNotFoundException())->setModel(Hold::class, [$hold->id]);
        }

        return DB::transaction(function () use ($hold): Hold {
            $lockedHold = Hold::query()
                ->lockForUpdate()
                ->findOrFail($hold->id);

            $lockedHold->update([
                'status' => HoldStatusEnum::EXPIRED,
                'expires_at' => now(),
            ]);

            return $lockedHold->refresh();
        }, 3);
    }

    public function updateHoldQuantity(User $user, Hold $hold, int $quantity): Hold
    {
        if ($hold->user_id !== $user->id) {
            throw (new ModelNotFoundException())->setModel(Hold::class, [$hold->id]);
        }

        return DB::transaction(function () use ($hold, $quantity): Hold {
            $lockedHold = Hold::query()
                ->with(['ticket.ticketType.event'])
                ->lockForUpdate()
                ->findOrFail($hold->id);

            $ticket = Ticket::query()
                ->with(['ticketType.event'])
                ->lockForUpdate()
                ->findOrFail($lockedHold->ticket_id);

            $targetQuantity = max(1, $quantity);

            if ($ticket->max_per_user !== null && $targetQuantity > $ticket->max_per_user) {
                throw ValidationException::withMessages([
                    'quantity' => 'Hai superato il limite massimo acquistabile per questo biglietto.',
                ]);
            }

            $availableQuantity = $ticket->getAvailableQuantity($lockedHold->id);

            if ($targetQuantity > $availableQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'La quantita richiesta non e piu disponibile.',
                ]);
            }

            $lockedHold->update([
                'quantity' => $targetQuantity,
                'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
                'status' => HoldStatusEnum::ACTIVE,
            ]);

            return $lockedHold->refresh();
        }, 3);
    }

    private function mapHold(Hold $hold): array
    {
        $ticket = $hold->ticket;
        $ticketType = $ticket->ticketType;
        $event = $ticketType->event;

        return [
            'id' => $hold->id,
            'quantity' => $hold->quantity,
            'status' => $hold->status?->value,
            'expires_at' => $hold->expires_at?->toIso8601String(),
            'remaining_seconds' => max(0, now()->diffInSeconds($hold->expires_at, false)),
            'ticket' => [
                'id' => $ticket->id,
                'price' => $ticket->price,
                'max_per_user' => $ticket->max_per_user,
                'available_quantity' => $ticket->getAvailableQuantity($hold->id),
            ],
            'ticket_type' => [
                'id' => $ticketType->id,
                'name' => $ticketType->name,
            ],
            'event' => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
            ],
        ];
    }
}
