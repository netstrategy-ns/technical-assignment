<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventQueueEntry;
use App\Repositories\EventQueueRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventQueueService
{
    public function __construct(
        private readonly EventQueueRepository $queues,
    ) {
    }

    public function enter(Event $event, int $userId): EventQueueEntry
    {
        if (!$event->queue_enabled) {
            throw new RuntimeException('Queue not enabled for this event.');
        }

        return DB::transaction(function () use ($event, $userId): EventQueueEntry {
            $now = Carbon::now();

            $existing = $this->queues->findForUser($event->id, $userId);
            if ($existing) {
                if (in_array($existing->status, ['completed', 'expired'], true)) {
                    $existing->status = 'waiting';
                    $existing->entered_at = $now;
                    $existing->allowed_until = null;
                    $existing->save();
                }

                return $existing;
            }

            return $this->queues->createWaiting($event->id, $userId, $now);
        });
    }

    public function status(Event $event, int $userId): ?EventQueueEntry
    {
        return $this->queues->findForUser($event->id, $userId);
    }

    public function allowNext(Event $event, int $allowedSeconds = 300): int
    {
        if (!$event->queue_enabled) {
            return 0;
        }

        $limit = $event->queue_max_concurrent ?? 50;
        if ($limit <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($event, $limit, $allowedSeconds): int {
            $now = Carbon::now();
            $allowedActive = $this->queues->countAllowedActive($event->id, $now);
            $availableSlots = max(0, $limit - $allowedActive);

            if ($availableSlots <= 0) {
                return 0;
            }

            $entries = $this->queues->nextWaiting($event->id, $availableSlots);

            foreach ($entries as $entry) {
                $entry->status = 'allowed';
                $entry->allowed_until = $now->copy()->addSeconds($allowedSeconds);
                $entry->save();
            }

            return $entries->count();
        });
    }

    public function expireAllowed(): int
    {
        return $this->queues->expireAllowed(Carbon::now());
    }

    public function markCompleted(Event $event, int $userId): void
    {
        if (!$event->queue_enabled) {
            return;
        }

        $this->queues->markCompleted($event->id, $userId);
    }

    public function assertAllowed(Event $event, int $userId): void
    {
        if (!$event->queue_enabled) {
            return;
        }

        $entry = $this->queues->findForUser($event->id, $userId);

        if (!$entry || $entry->status !== 'allowed') {
            throw new RuntimeException('User not allowed in queue.');
        }

        if ($entry->allowed_until && $entry->allowed_until->isPast()) {
            throw new RuntimeException('Queue access expired.');
        }
    }
}
