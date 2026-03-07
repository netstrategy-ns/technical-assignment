<?php

namespace App\Models;

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
}
