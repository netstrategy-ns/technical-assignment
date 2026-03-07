<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TicketType extends Model
{
    /** @use HasFactory<\Database\Factories\TicketTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'venue_type_id',
        'name',
    ];

    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     */

    // Relazione evento
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // Relazione venue type
    public function venueType(): BelongsTo
    {
        return $this->belongsTo(VenueType::class);
    }

    // Relazione biglietti
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Relazione order items passando per biglietti
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Ticket::class);
    }
}
