<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasAdminTable;
use Illuminate\Support\Str;
use App\Models\Scopes\Order\FilterScopes;
use App\Models\Scopes\Order\SortScopes;
use App\Support\Tables\OrderTableColumns;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    
    protected $fillable = [
        'user_id',
        'public_id',
        'status',
        'total_amount',
    ];

    protected $appends = [
        'event_titles',
        'event_categories',
    ];
    
    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'total_amount' => 'decimal:2',
        ];
    }
    
    // Usa public_id come identificatore di rotta dell'ordine
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
    
    
    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return OrderTableColumns::columns();
    }
    
    // Imposta automaticamente un UUID pubblico quando manca in creazione
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->public_id === null) {
                $order->public_id = (string) Str::uuid();
            }
        });
    }
    
    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     */

    // Relazione utente che ha effettuato l'ordine
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relazione alle righe ordine (ticket acquistati)
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Ritorna i titoli degli eventi dell'ordine, unici e concatenati
    public function getEventTitlesAttribute(): string
    {
        return $this->orderItems
            ->map(fn (OrderItem $item): ?string => $item->ticket?->ticketType?->event?->title)
            ->filter()
            ->unique()
            ->implode(', ');
    }

    // Ritorna le categorie degli eventi dell'ordine, uniche e concatenate
    public function getEventCategoriesAttribute(): string
    {
        return $this->orderItems
            ->map(fn (OrderItem $item): ?string => $item->ticket?->ticketType?->event?->category?->name)
            ->filter()
            ->unique()
            ->implode(', ');
    }

}
