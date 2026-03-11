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

    // Filtra ticket per nome evento
    public function scopeFilterByEventTitle(Builder $query, string $title): Builder
    {
        return $query->whereHas('ticketType', function (Builder $ticketTypeQuery) use ($title): Builder {
            return $ticketTypeQuery->whereHas('event', fn (Builder $eventQuery): Builder => $eventQuery->where('title', 'like', '%' . $title . '%'));
        });
    }

    // Filtra ticket per prezzo
    public function scopeFilterByPrice(Builder $query, int|float|string $price): Builder
    {
        return $query->where('price', (float) $price);
    }

    // Filtra ticket per data evento
    public function scopeFilterByEventDate(Builder $query, string $eventDate): Builder
    {
        return $query->whereHas('ticketType', function (Builder $ticketTypeQuery) use ($eventDate): Builder {
            return $ticketTypeQuery->whereHas('event', function (Builder $eventQuery) use ($eventDate): Builder {
                return $eventQuery->whereDate('starts_at', $eventDate);
            });
        });
    }

    // Filtra ticket per numero max per utente
    public function scopeFilterByMaxPerUser(Builder $query, int $maxPerUser): Builder
    {
        return $query->where('max_per_user', $maxPerUser);
    }
}
