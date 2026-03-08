<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_type_id',
        'price',
        'quantity_total',
        'max_per_user',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity_total' => 'integer',
        'max_per_user' => 'integer',
    ];

    /**
     * ------------------------------------------------------------
     * RELATTIONS
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

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    public function soldQuantity(): int
    {
        return (int) $this->orderItems()
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatusEnum::COMPLETED->value))
            ->sum('quantity');
    }

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

    public function getAvailableQuantity(?int $excludingHoldId = null): int
    {
        return max(
            0,
            $this->quantity_total - $this->soldQuantity() - $this->validHeldQuantity($excludingHoldId),
        );
    }
}
