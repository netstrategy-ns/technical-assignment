<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\QueueEntry;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\Event\FilterScopes;
use App\Models\Scopes\Event\SortScopes;
use App\Support\Tables\EventTableColumns;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    protected $fillable = [
        'title',
        'description',
        'event_category_id',
        'venue_type_id',
        'is_featured',
        'is_active',
        'starts_at',
        'ends_at',
        'location',
        'image_url',
        'sale_starts_at',
        'available_tickets',
        'queue_enabled',
        'queue_config',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sale_starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'queue_enabled' => 'boolean',
        'queue_config' => 'array',
    ];

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return EventTableColumns::columns();
    }

    /**
     * Risolve l'evento dallo slug nell'URL.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    /**
     * ------------------------------------------------------------
     * RELATTIONS
     * ------------------------------------------------------------
     */

    // Relazione per categoria evento
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    // Relazione per tipo di luogo dove si svolge l'evento
    public function venueType(): BelongsTo
    {
        return $this->belongsTo(VenueType::class, 'venue_type_id');
    }

    // Relazione tipologie biglietti
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    // Relazione quote biglietti
    public function ticketTypeQuotas(): HasManyThrough
    {
        return $this->hasManyThrough(TicketTypeQuota::class, TicketType::class, 'event_id', 'ticket_type_id', 'id', 'id');
    }

    // Relazione biglietti venduti passando per ticket types
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, TicketType::class);
    }

    // Relazione code attive
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    // Metodo per verificare se la coda è abilitata
    public function isQueueEnabled(): bool
    {
        return (bool) $this->queue_enabled;
    }

    // Metodo per ottenere il numero massimo di concorrenti per la coda
    public function getQueueMaxConcurrent(): int
    {
        $queueConfig = is_array($this->queue_config) ? $this->queue_config : [];

        return max(1, (int) ($queueConfig['max_concurrent'] ?? 1));
    }

    // Metodo per ottenere la durata della coda in minuti
    public function getQueueDurationMinutes(): int
    {
        $queueConfig = is_array($this->queue_config) ? $this->queue_config : [];

        return max(1, (int) ($queueConfig['duration_minutes'] ?? 10));
    }

    // Metodo per verificare se la prevendita non è iniziata
    public function isSaleNotStarted(): bool
    {
        return (bool) ($this->sale_starts_at?->isFuture());
    }
}
