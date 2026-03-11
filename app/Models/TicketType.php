<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\TicketType\FilterScopes;
use App\Models\Scopes\TicketType\SortScopes;
use App\Support\Tables\TicketTypeTableColumns;

class TicketType extends Model
{
    /** @use HasFactory<\Database\Factories\TicketTypeFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;



    protected $fillable = [
        'event_id',
        'venue_type_id',
        'name',
    ];

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return TicketTypeTableColumns::columns();
    }

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
    // Calcola quanti ticket sono disponibili tenendo conto di vendite e hold validi
    public function getAvailableQuantity(?int $excludingHoldId = null): int
    {
        $quota = (int) ($this->quota?->quantity ?? 0);
        $sold = $this->tickets->sum(fn (Ticket $ticket): int => $ticket->soldQuantity());
        $held = $this->tickets->sum(fn (Ticket $ticket): int => $ticket->validHeldQuantity($excludingHoldId));

        return max(0, $quota - $sold - $held);
    }

}
