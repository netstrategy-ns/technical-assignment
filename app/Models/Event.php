<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

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
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sale_starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Risolve l'evento dallo slug nell'URL.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Eventi con lo stesso titolo in date diverse hanno slug diversi.
     */
    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            if ($event->isDirty(['title', 'starts_at']) || empty($event->slug)) {
                $baseSlug = Str::slug($event->title) . '-' . $event->starts_at->format('Y-m-d');
                $slug = $baseSlug;
                $n = 1;
                while (true) {
                    $query = static::query()->where('slug', $slug);
                    if ($event->exists) {
                        $query->where('id', '!=', $event->id);
                    }
                    if (! $query->exists()) {
                        break;
                    }
                    $slug = $baseSlug . '-' . (++$n);
                }
                $event->slug = $slug;
            }
        });
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

    /**
     * Applica l'ordinamento scelto dalla UI (catalogo eventi).
     * Valori: date_asc, date_desc, featured_first.
     */
    public function scopeApplySort(Builder $query, string $sort = 'date_asc'): Builder
    {
        return match ($sort) {
            'date_desc' => $query->orderBy('starts_at', 'desc'),
            'featured_first' => $query->orderByRaw('CASE WHEN is_featured = 1 THEN 0 ELSE 1 END')->orderBy('starts_at'),
            default => $query->orderBy('starts_at', 'asc'),
        };
    }

    // Ordina eventi per disponibilità biglietti
    public function scopeOrderByAvailableTickets(Builder $query): Builder
    {
        return $query->orderBy('available_tickets', 'desc');
    }

}
