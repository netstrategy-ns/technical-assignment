<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueService
{
    public const QUEUE_ACCESS_DURATION_MINUTES = 15;

    /**
     * Join the queue for an event.
     *
     * @throws \RuntimeException
     */
    public function joinQueue(int $userId, Event $event): EventQueue
    {
        if (! $event->queue_enabled) {
            throw new \RuntimeException('Queue is not enabled for this event.');
        }

        // Check if user is already in queue
        $existing = EventQueue::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($userId, $event) {
            // Get next position
            $maxPosition = EventQueue::where('event_id', $event->id)
                ->lockForUpdate()
                ->max('position') ?? 0;

            return EventQueue::create([
                'event_id' => $event->id,
                'user_id' => $userId,
                'token' => Str::random(64),
                'position' => $maxPosition + 1,
                'status' => 'waiting',
            ]);
        });
    }

    /**
     * Get user's queue status for an event.
     */
    public function getStatus(int $userId, Event $event): ?array
    {
        $entry = EventQueue::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if (! $entry) {
            return null;
        }

        $aheadCount = 0;
        if ($entry->status === 'waiting') {
            $aheadCount = EventQueue::where('event_id', $event->id)
                ->where('position', '<', $entry->position)
                ->whereIn('status', ['waiting', 'active'])
                ->count();
        }

        return [
            'id' => $entry->id,
            'status' => $entry->status,
            'position' => $entry->position,
            'ahead' => $aheadCount,
            'token' => $entry->token,
            'expires_at' => $entry->expires_at?->toISOString(),
        ];
    }

    /**
     * Check if user has active queue access for an event.
     */
    public function hasAccess(int $userId, Event $event): bool
    {
        if (! $event->queue_enabled) {
            return true;
        }

        return EventQueue::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Activate next users in queue for all queue-enabled events.
     */
    public function processQueues(): int
    {
        $activated = 0;

        $events = Event::where('queue_enabled', true)->get();

        foreach ($events as $event) {
            $activated += $this->processEventQueue($event);
        }

        return $activated;
    }

    /**
     * Process the queue for a single event.
     */
    public function processEventQueue(Event $event): int
    {
        return DB::transaction(function () use ($event) {
            // Expire stale active entries
            EventQueue::where('event_id', $event->id)
                ->where('status', 'active')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            // Count current active users
            $activeCount = EventQueue::where('event_id', $event->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->count();

            // How many slots are open
            $slotsAvailable = max(0, $event->queue_concurrency_limit - $activeCount);

            if ($slotsAvailable === 0) {
                return 0;
            }

            // Get next waiting users
            $nextEntries = EventQueue::where('event_id', $event->id)
                ->where('status', 'waiting')
                ->orderBy('position')
                ->limit($slotsAvailable)
                ->lockForUpdate()
                ->get();

            $activated = 0;
            foreach ($nextEntries as $entry) {
                $entry->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'expires_at' => now()->addMinutes(self::QUEUE_ACCESS_DURATION_MINUTES),
                ]);
                $activated++;
            }

            return $activated;
        });
    }
}
