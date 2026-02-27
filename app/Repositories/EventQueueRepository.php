<?php

namespace App\Repositories;

use App\Models\EventQueueEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EventQueueRepository
{
    public function findForUser(int $eventId, int $userId): ?EventQueueEntry
    {
        return EventQueueEntry::query()
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();
    }

    public function createWaiting(int $eventId, int $userId, Carbon $now): EventQueueEntry
    {
        return EventQueueEntry::query()->create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'status' => 'waiting',
            'entered_at' => $now,
        ]);
    }

    public function nextWaiting(int $eventId, int $limit): Collection
    {
        return EventQueueEntry::query()
            ->where('event_id', $eventId)
            ->where('status', 'waiting')
            ->orderBy('entered_at')
            ->limit($limit)
            ->lockForUpdate()
            ->get();
    }

    public function countAllowedActive(int $eventId, Carbon $now): int
    {
        return EventQueueEntry::query()
            ->where('event_id', $eventId)
            ->where('status', 'allowed')
            ->where('allowed_until', '>', $now)
            ->count();
    }

    public function expireAllowed(Carbon $now): int
    {
        return EventQueueEntry::query()
            ->where('status', 'allowed')
            ->where('allowed_until', '<=', $now)
            ->update([
                'status' => 'expired',
                'updated_at' => $now,
            ]);
    }
}
