<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\OrderItem\FilterScopes;
use App\Models\Scopes\OrderItem\SortScopes;
use App\Support\Tables\OrderItemTableColumns;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    protected $fillable = [
        'order_id',
        'ticket_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return OrderItemTableColumns::columns();
    }

    /**
     * ------------------------------------------------------------
     * RELATIONS
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
