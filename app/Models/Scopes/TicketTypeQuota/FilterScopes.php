<?php

namespace App\Models\Scopes\TicketTypeQuota;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra quote per nome tipologia
    public function scopeFilterByTicketTypeName(Builder $query, string $ticketTypeName): Builder
    {
        return $query->whereHas('ticketType', fn (Builder $relatedQuery): Builder => $relatedQuery->where('name', 'like', '%' . $ticketTypeName . '%'));
    }

    // Filtra quote per titolo evento
    public function scopeFilterByEventTitle(Builder $query, string $eventTitle): Builder
    {
        return $query->whereHas('ticketType.event', fn (Builder $relatedQuery): Builder => $relatedQuery->where('title', 'like', '%' . $eventTitle . '%'));
    }

    // Filtra quote per quantità
    public function scopeFilterByQuantity(Builder $query, int $quantity): Builder
    {
        return $query->where('quantity', $quantity);
    }
}
