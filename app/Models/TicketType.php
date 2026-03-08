<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    // Relazione quota biglietti
    public function quota(): HasOne
    {
        return $this->hasOne(TicketTypeQuota::class);
    }

    // Relazione order items passando per biglietti
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Ticket::class);
    }


    /**
     * ------------------------------------------------------------
     * HELPER METHODS
     * ------------------------------------------------------------
     */
    public function getAvailableQuantity(): int
    {
        $quota = (int) ($this->quota?->quantity ?? 0);
        $sold = $this->tickets->sum(fn (Ticket $ticket): int => $ticket->soldQuantity());
        $held = $this->tickets->sum(fn (Ticket $ticket): int => $ticket->validHeldQuantity());

        return max(0, $quota - $sold - $held);
    }

}
