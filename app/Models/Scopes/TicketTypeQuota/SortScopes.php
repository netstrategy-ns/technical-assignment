<?php

namespace App\Models\Scopes\TicketTypeQuota;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina quote per nome tipologia
    public function scopeSortByTicketTypeName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('ticket_types as tt', 'tt.id', '=', 'ticket_type_quotas.ticket_type_id')
            ->orderBy('tt.name', $direction)
            ->select('ticket_type_quotas.*');
    }

    // Ordina quote per titolo evento
    public function scopeSortByEventTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('ticket_types as tt2', 'tt2.id', '=', 'ticket_type_quotas.ticket_type_id')
            ->leftJoin('events as e', 'e.id', '=', 'tt2.event_id')
            ->orderBy('e.title', $direction)
            ->select('ticket_type_quotas.*');
    }

    // Ordina quote per quantità
    public function scopeSortByQuantity(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('quantity', $direction);
    }

    // Ordina quote per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }
}
