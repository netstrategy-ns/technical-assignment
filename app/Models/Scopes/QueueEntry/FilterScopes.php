<?php

namespace App\Models\Scopes\QueueEntry;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra code per titolo evento
    public function scopeFilterByEventTitle(Builder $query, string $eventTitle): Builder
    {
        return $query->whereHas('event', fn (Builder $relatedQuery): Builder => $relatedQuery->where('title', 'like', '%' . $eventTitle . '%'));
    }

    // Filtra code per nome utente
    public function scopeFilterByUserName(Builder $query, string $userName): Builder
    {
        return $query->whereHas('user', fn (Builder $relatedQuery): Builder => $relatedQuery->where('name', 'like', '%' . $userName . '%'));
    }

    // Filtra code per stato
    public function scopeFilterByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Filtra code per data ingresso
    public function scopeFilterByEnteredAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('entered_at', $date);
    }

    // Filtra code per data abilitazione
    public function scopeFilterByEnabledAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('enabled_at', $date);
    }
}
