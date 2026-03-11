<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\Ticket\FilterScopes;
use App\Models\Scopes\Ticket\SortScopes;
use App\Support\Tables\TicketTableColumns;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    protected $fillable = [
        'ticket_type_id',
        'price',
        'max_per_user',
    ];

    protected $appends = [
        'event_title',
        'availability',
        'event_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'max_per_user' => 'integer',
    ];

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return TicketTableColumns::columns();
    }

    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     */

    // Relazione ticket type
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    // Relazione order items
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relazione agli hold legati a questo ticket
    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    // Restituisce il titolo evento del ticket
    public function getEventTitleAttribute(): ?string
    {
        return $this->ticketType?->event?->title;
    }

    // Restituisce la data dell'evento in formato d/m/Y
    public function getEventDateAttribute(): ?string
    {
        return $this->ticketType?->event?->starts_at?->format('d/m/Y');
    }

    // Calcola disponibilità come quota meno quantità già acquistata
    public function getAvailabilityAttribute(): int
    {
        $quota = (int) ($this->ticketType?->quota?->quantity ?? 0);
        $purchased = (int) ($this->purchased_quantity ?? 0);

        return max(0, $quota - $purchased);
    }

    

    // Calcola i pezzi venduti con ordini in stato COMPLETED
    public function soldQuantity(): int
    {
        return (int) $this->orderItems()
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatusEnum::COMPLETED->value))
            ->sum('quantity');
    }

    // Calcola la quantità trattenuta da hold attivi, eventualmente escludendo un hold
    public function validHeldQuantity(?int $excludingHoldId = null): int
    {
        return (int) $this->holds()
            ->active()
            ->valid()
            ->when(
                $excludingHoldId !== null,
                fn ($query) => $query->whereKeyNot($excludingHoldId),
            )
            ->sum('quantity');
    }

    // Restituisce la disponibilità calcolata delegando al ticket type
    public function getAvailableQuantity(?int $excludingHoldId = null): int
    {
        return max(0, (int) $this->ticketType?->getAvailableQuantity($excludingHoldId));
    }
}
