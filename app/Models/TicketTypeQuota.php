<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class TicketTypeQuota extends Model
{
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

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /** Evento (tramite TicketType): allocation → ticketType → event. */
    public function event(): HasOneThrough
    {
        return $this->hasOneThrough(Event::class, TicketType::class, 'id', 'id', 'ticket_type_id', 'event_id');
    }
}
