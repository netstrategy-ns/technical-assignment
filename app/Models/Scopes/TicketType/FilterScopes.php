<?php

namespace App\Models\Scopes\TicketType;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra tipologie biglietto per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra tipologie biglietto per nome
    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    // Filtra tipologie biglietto per titolo evento
    public function scopeFilterByEventTitle(Builder $query, string $eventTitle): Builder
    {
        return $query->whereHas('event', fn (Builder $relatedQuery): Builder => $relatedQuery->where('title', 'like', '%' . $eventTitle . '%'));
    }

    // Filtra tipologie biglietto per tipologia luogo
    public function scopeFilterByVenueTypeName(Builder $query, string $venueTypeName): Builder
    {
        return $query->whereHas('venueType', fn (Builder $relatedQuery): Builder => $relatedQuery->where('name', 'like', '%' . $venueTypeName . '%'));
    }
}
