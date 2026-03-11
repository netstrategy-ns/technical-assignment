<?php

namespace App\Models\Scopes\TicketType;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina tipologie biglietto per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina tipologie biglietto per nome
    public function scopeSortByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    // Ordina tipologie biglietto per titolo evento
    public function scopeSortByEventTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('events as e', 'e.id', '=', 'ticket_types.event_id')
            ->orderBy('e.title', $direction)
            ->select('ticket_types.*');
    }

    // Ordina tipologie biglietto per tipologia luogo
    public function scopeSortByVenueTypeName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('venue_types as vt', 'vt.id', '=', 'ticket_types.venue_type_id')
            ->orderBy('vt.name', $direction)
            ->select('ticket_types.*');
    }
}
