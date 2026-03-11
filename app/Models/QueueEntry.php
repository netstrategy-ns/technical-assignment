<?php

namespace App\Models;

use App\Enums\QueueEntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\QueueEntry\FilterScopes;
use App\Models\Scopes\QueueEntry\SortScopes;
use App\Support\Tables\QueueEntryTableColumns;

class QueueEntry extends Model
{
    /** @use HasFactory<\Database\Factories\QueueEntryFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'entered_at',
        'enabled_at',
        'enabled_until',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
            'enabled_at' => 'datetime',
            'enabled_until' => 'datetime',
            'status' => QueueEntryStatus::class,
        ];
    }

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return QueueEntryTableColumns::columns();
    }

    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     */

    // Relazione utente
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relazione evento
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

}
