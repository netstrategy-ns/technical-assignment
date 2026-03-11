<?php

namespace App\Models\Scopes\Ticket;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra ticket per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra ticket per nome tipologia
    public function scopeFilterByTicketTypeName(Builder $query, string $name): Builder
    {
        return $query->whereHas('ticketType', fn (Builder $relatedQuery): Builder => $relatedQuery->where('name', 'like', '%' . $name . '%'));
    }

    // Filtra ticket per prezzo
    public function scopeFilterByPrice(Builder $query, int|float|string $price): Builder
    {
        return $query->where('price', (float) $price);
    }

    // Filtra ticket per numero max per utente
    public function scopeFilterByMaxPerUser(Builder $query, int $maxPerUser): Builder
    {
        return $query->where('max_per_user', $maxPerUser);
    }
}
