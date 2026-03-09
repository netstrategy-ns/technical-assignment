<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param array<int, int> $holdIds
     */
    public function checkout(User $user, array $holdIds = []): Order
    {
        $normalizedHoldIds = $this->normalizeHoldIds($holdIds);

        return DB::transaction(function () use ($user, $normalizedHoldIds): Order {
            $holds = $this->resolveHolds($user, $normalizedHoldIds);

            if ($holds->isEmpty()) {
                throw ValidationException::withMessages([
                    'holds' => 'Nessun biglietto valido nel carrello.',
                ]);
            }

            $this->validateHoldsAreCurrent($holds, $normalizedHoldIds);

            $ticketIds = $holds->pluck('ticket_id')->unique()->values()->all();
            $tickets = Ticket::query()
                ->whereIn('id', $ticketIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($tickets->count() !== count($ticketIds)) {
                throw ValidationException::withMessages([
                    'holds' => 'Uno o più biglietti non sono disponibili al momento.',
                ]);
            }

            $order = $user->orders()->create([
                'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => $this->computeTotalAmount($holds, $tickets),
            ]);

            foreach ($holds as $hold) {
                $ticket = $tickets->get($hold->ticket_id);
                if ($ticket === null) {
                    throw ValidationException::withMessages([
                        'holds' => 'Uno o più biglietti non sono disponibili al momento.',
                    ]);
                }

                $order->orderItems()->create([
                    'ticket_id' => $hold->ticket_id,
                    'quantity' => $hold->quantity,
                    'unit_price' => $ticket->price,
                ]);

                $hold->update([
                    'status' => HoldStatusEnum::COMPLETED,
                ]);
            }

            return $order;
        });
    }

    /**
     * @param array<int, int> $holdIds
     * @return array<int, int>
     */
    private function normalizeHoldIds(array $holdIds): array
    {
        return array_values(array_unique(
            array_map('intval', array_filter($holdIds, fn (mixed $id): bool => (int) $id > 0)),
        ));
    }

    /**
     * @param array<int, int> $holdIds
     *
     * @return Collection<int, Hold>
     */
    private function resolveHolds(User $user, array $holdIds): Collection
    {
        if ($holdIds === []) {
            return Hold::query()
                ->with('ticket')
                ->where('user_id', $user->id)
                ->active()
                ->valid()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
        }

        $requestedHolds = Hold::query()
            ->where('user_id', $user->id)
            ->with('ticket')
            ->whereIn('id', $holdIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($requestedHolds->count() !== count($holdIds)) {
            throw ValidationException::withMessages([
                'holds' => 'Uno o più biglietti non sono validi o non ti appartengono.',
            ]);
        }

        return $requestedHolds;
    }

    /**
     * @param Collection<int, Hold> $holds
     * @param array<int, int> $holdIds
     */
    private function validateHoldsAreCurrent(Collection $holds, array $holdIds): void
    {
        if (empty($holdIds)) {
            return;
        }

        $invalidCount = $holds
            ->filter(static fn (Hold $hold): bool => ! $hold->isValid())
            ->count();

        if ($invalidCount > 0) {
            throw ValidationException::withMessages([
                'holds' => 'Uno o più biglietti non sono più disponibili.',
            ]);
        }
    }

    private function computeTotalAmount(Collection $holds, Collection $tickets): string
    {
        $amount = 0.0;
        foreach ($holds as $hold) {
            $ticket = $tickets->get($hold->ticket_id);
            if ($ticket === null) {
                throw ValidationException::withMessages([
                    'holds' => 'Uno o più biglietti non sono disponibili al momento.',
                ]);
            }

            $amount += (float) $ticket->price * $hold->quantity;
        }

        return number_format($amount, 2, '.', '');
    }
}
