<?php

namespace App\Models\Scopes\QueueEntry;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina code per titolo evento
    public function scopeSortByEventTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('events as e', 'e.id', '=', 'queue_entries.event_id')
            ->orderBy('e.title', $direction)
            ->select('queue_entries.*');
    }

    // Ordina code per nome utente
    public function scopeSortByUserName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('users as u', 'u.id', '=', 'queue_entries.user_id')
            ->orderBy('u.name', $direction)
            ->select('queue_entries.*');
    }

    // Ordina code per stato
    public function scopeSortByStatus(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('status', $direction);
    }

    // Ordina code per data ingresso
    public function scopeSortByEnteredAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('entered_at', $direction);
    }

    // Ordina code per data abilitazione
    public function scopeSortByEnabledAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('enabled_at', $direction);
    }

    // Ordina code per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }
}
