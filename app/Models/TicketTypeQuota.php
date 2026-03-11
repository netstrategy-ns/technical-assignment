<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\TicketTypeQuota\FilterScopes;
use App\Models\Scopes\TicketTypeQuota\SortScopes;
use App\Support\Tables\TicketTypeQuotaTableColumns;

class TicketTypeQuota extends Model
{

    /** @use HasFactory<\Database\Factories\TicketTypeQuotaFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    protected $fillable = [
        'ticket_type_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return TicketTypeQuotaTableColumns::columns();
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

    // Relazione evento passando per ticket type
    public function event(): HasOneThrough
    {
        return $this->hasOneThrough(Event::class, TicketType::class, 'id', 'id', 'ticket_type_id', 'event_id');
    }

    
}
