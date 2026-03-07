<?php

namespace App\Observers;

use App\Models\TicketTypeQuota;

class TicketTypeQuotaObserver
{
    // Valida la somma dei bigliettti dispobili per tipo evitando di superare il totale disponibile per evento 
    public function saving(TicketTypeQuota $allocation): void
    {
        $allocation->loadMissing('ticketType.event');
        $event = $allocation->ticketType?->event;

        // Controllo se l'evento esiste e se la disponibilità è maggiore di 0
        if (! $event || (int) $event->available_tickets <= 0) {
            return;
        }

        $totalAllowed = (int) $event->available_tickets;
        $currentSum = (int) $event->ticketTypes()
            ->join('ticket_type_quotas', 'ticket_types.id', '=', 'ticket_type_quotas.ticket_type_id')
            ->where('ticket_types.event_id', $event->id)
            ->sum('ticket_type_quotas.quantity');
        $oldQty = $allocation->exists ? (int) $allocation->getOriginal('quantity') : 0;
        $totalAfter = $currentSum - $oldQty + (int) $allocation->quantity;

        if ($totalAfter > $totalAllowed) {
            throw new \InvalidArgumentException(
                "La somma dei biglietti per tipologia non può superare il totale evento ({$totalAllowed}). Totale dopo modifica: {$totalAfter}."
            );
        }
    }
}
