<?php

namespace App\Models;

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
}
