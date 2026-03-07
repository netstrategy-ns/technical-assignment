<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class TicketTypeQuota extends Model
{

    /** @use HasFactory<\Database\Factories\TicketTypeQuotaFactory> */
    use HasFactory;

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
