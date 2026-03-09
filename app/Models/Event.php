<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\QueueEntry;
class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_category_id',
        'venue_type_id',
        'is_featured',
        'is_active',
        'starts_at',
        'ends_at',
        'location',
        'image_url',
        'sale_starts_at',
        'available_tickets',
        'queue_enabled',
        'queue_config',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sale_starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'queue_enabled' => 'boolean',
        'queue_config' => 'array',
    ];

    /**
     * Risolve l'evento dallo slug nell'URL.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    /**
     * ------------------------------------------------------------
     * RELATTIONS
     * ------------------------------------------------------------
     */

    /** @return BelongsTo<EventCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    // Relazione per tipo di luogo dove si svolge l'evento
    public function venueType(): BelongsTo
    {
        return $this->belongsTo(VenueType::class);
    }

    // Relazione tipologie biglietti
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    // Relazione quote biglietti
    public function ticketTypeQuotas(): HasManyThrough
    {
        return $this->hasManyThrough(TicketTypeQuota::class, TicketType::class, 'event_id', 'ticket_type_id', 'id', 'id');
    }

    // Relazione biglietti venduti passando per ticket types
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, TicketType::class);
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function isQueueEnabled(): bool
    {
        return (bool) $this->queue_enabled;
    }

    public function getQueueMaxConcurrent(): int
    {
        $queueConfig = is_array($this->queue_config) ? $this->queue_config : [];

        return max(1, (int) ($queueConfig['max_concurrent'] ?? 1));
    }

    public function getQueueDurationMinutes(): int
    {
        $queueConfig = is_array($this->queue_config) ? $this->queue_config : [];

        return max(1, (int) ($queueConfig['duration_minutes'] ?? 10));
    }

    /**
     * ------------------------------------------------------------
     * SCOPES
     * ------------------------------------------------------------
     */

    // Cerca eventi per titolo
    public function scopeSearchByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'like', '%' . $title . '%');
    }

    // Filtra eventi per categoria
    public function scopeFilterByCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
    }

    // Filtra eventi per luogo
    public function scopeFilterByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    // Filtra eventi featured
    public function scopeFilterByFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // Filtra eventi attivi
    public function scopeFilterByActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Filtra eventi con almeno un biglietto disponibile
    public function scopeFilterByAvailableTickets(Builder $query): Builder
    {
        return $query->where('available_tickets', '>', 0);
    }

    // Filtra eventi per data di inizio
    public function scopeFilterByStartDate(Builder $query, string $startDate): Builder
    {
        return $query->where('starts_at', '>=', $startDate);
    }

    // Filtra eventi tra due date
    public function scopeFilterByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('starts_at', [$startDate, $endDate]);
    }

    // Ordina eventi per data di inizio
    public function scopeOrderByStartDate(Builder $query): Builder
    {
        return $query->orderBy('starts_at');
    }


    // Ordina gli eventi per data o per eventi in evidenza
    public function scopeApplySort(Builder $query, string $sort = 'date_asc'): Builder
    {
        return match ($sort) {
            'date_desc' => $query->orderBy('starts_at', 'desc'),
            'featured_first' => $query->orderByRaw('CASE WHEN is_featured = 1 THEN 0 ELSE 1 END')->orderBy('starts_at'),
            default => $query->orderBy('starts_at', 'asc'),
        };
    }

    public function isSaleNotStarted(): bool
    {
        return (bool) ($this->sale_starts_at?->isFuture());
    }

    // Raggruppa gli scopes per filtrare gli eventi
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
            ->when($filters['search'] ?? null, fn(Builder $q, string $search): Builder => $q->searchByTitle($search))
            ->when($filters['category'] ?? null, fn(Builder $q, string $categorySlug): Builder => $q->filterByCategory($categorySlug))
            ->when($filters['location'] ?? null, fn(Builder $q, string $location): Builder => $q->filterByLocation($location))
            ->when($filters['available_tickets'] ?? false, fn(Builder $q): Builder => $q->filterByAvailableTickets())
            ->when($filters['featured'] ?? false, fn(Builder $q): Builder => $q->filterByFeatured());
    }

}
