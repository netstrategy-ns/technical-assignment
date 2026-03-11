<?php

namespace App\Models;

use App\Enums\HoldStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\Hold\FilterScopes;

class Hold extends Model
{
    /** @use HasFactory<\Database\Factories\HoldFactory> */
    use HasFactory;
    use FilterScopes;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'quantity',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expires_at' => 'datetime',
            'status' => HoldStatusEnum::class,
        ];
    }

    // Relazione utente proprietario del trattenimento
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relazione al ticket associato al trattenimento
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Verifica se l'hold è valido: stato attivo + scadenza nel futuro
    public function isValid(): bool
    {
        return $this->status === HoldStatusEnum::ACTIVE
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    // Verifica se l'hold risulta non valido (scaduto o non attivo)
    public function isExpired(): bool
    {
        return ! $this->isValid();
    }
}
