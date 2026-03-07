<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'ticket_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /**
     * ------------------------------------------------------------
     * RELATTIONS
     * ------------------------------------------------------------
     */

    // Relazione ordine
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relazione Biglietto
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
