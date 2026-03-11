<?php

namespace App\Models\Scopes\Event;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra eventi per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra eventi per titolo
    public function scopeFilterByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'like', '%' . $title . '%');
    }

    // Filtra eventi per categoria (nome)
    public function scopeFilterByCategoryName(Builder $query, string $categoryName): Builder
    {
        return $query->whereHas(
            'category',
            fn (Builder $categoryQuery): Builder => $categoryQuery->where('name', 'like', '%' . $categoryName . '%'),
        );
    }

    // Filtra eventi per categoria (slug)
    public function scopeFilterByCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
    }

    // Filtra eventi in evidenza
    public function scopeFilterByFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // Filtra eventi in evidenza con valore booleano
    public function scopeFilterByIsFeatured(Builder $query, bool $isFeatured): Builder
    {
        return $query->where('is_featured', $isFeatured);
    }

    // Filtra eventi attivi
    public function scopeFilterByActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Filtra eventi con coda abilitata
    public function scopeFilterByQueueEnabled(Builder $query, bool $enabled): Builder
    {
        return $query->where('queue_enabled', $enabled);
    }

    // Filtra eventi con almeno un biglietto disponibile
    public function scopeFilterByAvailableTickets(Builder $query, bool $hasAvailableTickets = true): Builder
    {
        return $hasAvailableTickets ? $query->where('available_tickets', '>', 0) : $query;
    }

    // Filtra eventi per luogo
    public function scopeFilterByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    // Filtra eventi per data di inizio
    public function scopeFilterByStartsAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('starts_at', $date);
    }

    // Filtra eventi per data di fine
    public function scopeFilterByEndsAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('ends_at', $date);
    }

    // Filtra eventi per data apertura vendite
    public function scopeFilterBySaleStartsAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('sale_starts_at', $date);
    }

    // Filtra eventi per tipologia luogo
    public function scopeFilterByVenueTypeName(Builder $query, string $venueTypeName): Builder
    {
        return $query->whereHas(
            'venueType',
            fn (Builder $venueTypeQuery): Builder => $venueTypeQuery->where('name', 'like', '%' . $venueTypeName . '%'),
        );
    }

    // Filtra eventi con data di inizio non inferiore a un valore
    public function scopeFilterByStartDate(Builder $query, string $startDate): Builder
    {
        return $query->where('starts_at', '>=', $startDate);
    }

    // Filtra eventi tra due date
    public function scopeFilterByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('starts_at', [$startDate, $endDate]);
    }

    /**
     * Raggruppa filtri personalizzati degli eventi
     */
    public function scopeApplyFilters(Builder $query, array $filters = []): Builder
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if ($startDate !== null && $endDate !== null) {
            $query->whereBetween('starts_at', [$startDate, $endDate]);
        } elseif ($startDate !== null) {
            $query->filterByStartDate($startDate);
        } elseif ($endDate !== null) {
            $query->where('starts_at', '<=', $endDate);
        }

        return $query
            ->when($filters['search'] ?? null, fn(Builder $q, string $search): Builder => $q->filterByTitle($search))
            ->when($filters['category'] ?? null, fn(Builder $q, string $categorySlug): Builder => $q->filterByCategory($categorySlug))
            ->when($filters['location'] ?? null, fn(Builder $q, string $location): Builder => $q->filterByLocation($location))
            ->when($filters['available_tickets'] ?? false, fn(Builder $q): Builder => $q->filterByAvailableTickets())
            ->when($filters['featured'] ?? false, fn(Builder $q): Builder => $q->filterByFeatured());
    }
}
