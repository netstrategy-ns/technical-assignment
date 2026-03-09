<?php

namespace App\Models;

use App\Enums\QueueEntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    /** @use HasFactory<\Database\Factories\QueueEntryFactory> */
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
