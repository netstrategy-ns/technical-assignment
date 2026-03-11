<?php

namespace App\Models\Scopes\Event;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina eventi per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina eventi per titolo
    public function scopeSortByTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('title', $direction);
    }

    // Ordina eventi per categoria
    public function scopeSortByCategoryName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('event_categories as ec', 'ec.id', '=', 'events.event_category_id')
            ->orderBy('ec.name', $direction)
            ->select('events.*');
    }

    // Ordina eventi per tipologia luogo
    public function scopeSortByVenueTypeName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('venue_types as vt', 'vt.id', '=', 'events.venue_type_id')
            ->orderBy('vt.name', $direction)
            ->select('events.*');
    }

    // Ordina eventi per data di inizio
    public function scopeSortByStartsAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('starts_at', $direction);
    }

    // Ordina eventi per data di fine
    public function scopeSortByEndsAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('ends_at', $direction);
    }

    // Ordina eventi per data apertura vendite
    public function scopeSortBySaleStartsAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('sale_starts_at', $direction);
    }

    // Ordina eventi per luogo
    public function scopeSortByLocation(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('location', $direction);
    }

    // Ordina eventi in evidenza
    public function scopeSortByIsFeatured(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('is_featured', $direction);
    }

    // Ordina eventi con coda attiva
    public function scopeSortByQueueEnabled(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('queue_enabled', $direction);
    }

    // Ordina eventi per numero di code attive
    public function scopeSortByQueueEntriesCount(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->withCount('queueEntries')->orderBy('queue_entries_count', $direction);
    }

    // Ordina eventi per biglietti disponibili
    public function scopeSortByAvailableTickets(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('available_tickets', $direction);
    }

    // Ordina eventi per data di inizio
    public function scopeOrderByStartDate(Builder $query): Builder
    {
        return $query->orderBy('starts_at');
    }

    // Ordina eventi con regola personalizzata
    public function scopeApplySort(Builder $query, string $sort = 'date_asc'): Builder
    {
        return match ($sort) {
            'date_desc' => $query->orderBy('starts_at', 'desc'),
            'featured_first' => $query->orderByRaw('CASE WHEN is_featured = 1 THEN 0 ELSE 1 END')->orderBy('starts_at'),
            default => $query->orderBy('starts_at', 'asc'),
        };
    }
}
