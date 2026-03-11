<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Ticket;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartHoldService
{
    public const HOLD_MINUTES = 10;

    public function __construct(
        private readonly QueueService $queueService,
    ) {
    }

    // Costruisce il payload del carrello dell'utente con totale pezzi e importo
    public function buildCartPayload(User $user): array
    {
        $items = Hold::query()
            ->with(['ticket.ticketType.event'])
            ->whereBelongsTo($user)
            ->active()
            ->valid()
            ->orderBy('expires_at')
            ->get()
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

    // Crea o aggiorna un hold attivo per l'utente e ticket specificato
    public function placeHold(User $user, int $ticketId, int $quantity): Hold
    {
        return DB::transaction(function () use ($user, $ticketId, $quantity): Hold {
            $ticket = $this->findTicket($ticketId);
            $this->validateTicketForHold($ticket);
            $this->queueService->assertUserCanHold($user, $ticket->ticketType?->event);

            $latestHold = Hold::query()
                ->whereBelongsTo($user)
                ->where('ticket_id', $ticket->id)
                ->where('status', '!=', HoldStatusEnum::COMPLETED->value)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($latestHold !== null) {
                Hold::query()
                    ->whereBelongsTo($user)
                    ->where('ticket_id', $ticket->id)
                    ->whereKeyNot($latestHold->id)
                    ->where('status', '!=', HoldStatusEnum::COMPLETED->value)
                    ->update([
                        'status' => HoldStatusEnum::EXPIRED->value,
                        'expires_at' => now(),
                    ]);
            }

            $targetQuantity = max(1, $quantity) + ($latestHold?->isValid() ? $latestHold->quantity : 0);
            $this->assertQuantityCanBeHeld($ticket, $targetQuantity, $latestHold?->id);

            $hold = $latestHold ?? new Hold([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
            ]);

            $this->activateHold($hold, $targetQuantity);
            $this->queueService->refreshEnabledWindowForUserAndEvent($user, $ticket->ticketType?->event);

            return $hold->fresh(['ticket.ticketType.event']);
        }, 3);
    }

    // Rilascia (scade) un hold appartenente all'utente
    public function releaseHold(User $user, Hold $hold): Hold
    {
        return DB::transaction(function () use ($user, $hold): Hold {
            $lockedHold = $this->findOwnedLockedHold($user, $hold);

            $lockedHold->update([
                'status' => HoldStatusEnum::EXPIRED,
                'expires_at' => now(),
            ]);

            return $lockedHold->refresh();
        }, 3);
    }

    // Aggiorna quantità di un hold con validazioni e riattivazione
    public function updateHoldQuantity(User $user, Hold $hold, int $quantity): Hold
    {
        return DB::transaction(function () use ($user, $hold, $quantity): Hold {
            $lockedHold = $this->findOwnedLockedHold($user, $hold);

            if ($lockedHold->isExpired()) {
                throw ValidationException::withMessages([
                    'quantity' => 'La prenotazione e scaduta. Aggiungi di nuovo i biglietti al carrello.',
                ]);
            }

            $ticket = $this->findTicket($lockedHold->ticket_id);
            $this->queueService->assertUserCanHold($user, $ticket->ticketType?->event);
            $targetQuantity = max(1, $quantity);
            $this->assertQuantityCanBeHeld($ticket, $targetQuantity, $lockedHold->id);
            $this->activateHold($lockedHold, $targetQuantity);
            $this->queueService->refreshEnabledWindowForUserAndEvent($user, $ticket->ticketType?->event);

            return $lockedHold->refresh();
        }, 3);
    }

    // Recupera un ticket con lock e lancia errore se non esiste
    private function findTicket(int $ticketId): Ticket
    {
        $ticket = Ticket::query()
            ->with(['ticketType.event'])
            ->lockForUpdate()
            ->find($ticketId);

        if (! $ticket) {
            throw (new ModelNotFoundException())->setModel(Ticket::class, [$ticketId]);
        }

        return $ticket;
    }

    // Valida che il ticket abbia un evento valido e la vendita sia avviata
    private function validateTicketForHold(Ticket $ticket): void
    {
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
    }

    // Recupera e blocca l'hold, verificando proprietà utente
    private function findOwnedLockedHold(User $user, Hold $hold): Hold
    {
        if ($hold->user_id !== $user->id) {
            throw (new ModelNotFoundException())->setModel(Hold::class, [$hold->id]);
        }

        return Hold::query()
            ->with(['ticket.ticketType.event'])
            ->lockForUpdate()
            ->findOrFail($hold->id);
    }

    // Verifica limiti utente e disponibilità prima di mantenere un hold
    private function assertQuantityCanBeHeld(Ticket $ticket, int $targetQuantity, ?int $excludingHoldId = null): void
    {
        if ($ticket->max_per_user !== null && $targetQuantity > $ticket->max_per_user) {
            throw ValidationException::withMessages([
                'quantity' => 'Hai superato il limite massimo acquistabile per questo biglietto.',
            ]);
        }

        if ($targetQuantity > $ticket->getAvailableQuantity($excludingHoldId)) {
            throw ValidationException::withMessages([
                'quantity' => 'La quantita richiesta non e piu disponibile.',
            ]);
        }
    }

    // Attiva o riattiva l'hold impostando quantità, scadenza e stato
    private function activateHold(Hold $hold, int $quantity): void
    {
        $hold->fill([
            'quantity' => $quantity,
            'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
            'status' => HoldStatusEnum::ACTIVE,
        ])->save();
    }

    // Serializza un hold nel formato inviato al frontend
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
