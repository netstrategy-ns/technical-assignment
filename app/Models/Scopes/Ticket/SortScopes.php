<?php

namespace App\Models\Scopes\Ticket;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina ticket per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina ticket per nome tipologia
    public function scopeSortByTicketTypeName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('ticket_types as tt', 'tt.id', '=', 'tickets.ticket_type_id')
            ->orderBy('tt.name', $direction)
            ->select('tickets.*');
    }

    // Ordina ticket per evento
    public function scopeSortByEventTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('ticket_types as tt', 'tt.id', '=', 'tickets.ticket_type_id')
            ->leftJoin('events as e', 'e.id', '=', 'tt.event_id')
            ->orderBy('e.title', $direction)
            ->select('tickets.*');
    }

    // Ordina ticket per prezzo
    public function scopeSortByPrice(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('price', $direction);
    }

    // Ordina ticket per max per utente
    public function scopeSortByMaxPerUser(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('max_per_user', $direction);
    }
}
