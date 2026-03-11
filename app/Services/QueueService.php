<?php

namespace App\Services;

use App\Enums\QueueEntryStatus;
use Carbon\CarbonInterface;
use App\Enums\HoldStatusEnum;
use App\Models\Event;
use App\Models\Hold;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueService
{
    public const DEFAULT_MAX_CONCURRENT = 1;
    public const DEFAULT_DURATION_MINUTES = 10;

    // Restituisce la config della coda (max concurrent e durata) con fallback ai default
    public function getQueueConfig(Event $event): array
    {
        $queueConfig = is_array($event->queue_config) ? $event->queue_config : [];

        return [
            'max_concurrent' => max(1, (int) ($queueConfig['max_concurrent'] ?? self::DEFAULT_MAX_CONCURRENT)),
            'duration_minutes' => max(1, (int) ($queueConfig['duration_minutes'] ?? self::DEFAULT_DURATION_MINUTES)),
        ];
    }

    // Partecipa o crea l'entry in coda dell'utente per l'evento
    public function joinQueue(Event $event, User $user): QueueEntry
    {
        if (! $event->isQueueEnabled()) {
            throw ValidationException::withMessages([
                'queue' => 'Questo evento non utilizza la coda.',
            ]);
        }

        return DB::transaction(function () use ($event, $user): QueueEntry {
            $this->expireEnabledEntries($event);
            $this->promoteWaitingEntries($event);

            $entry = QueueEntry::query()
                ->where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('status', '!=', QueueEntryStatus::COMPLETED->value)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($entry !== null && $entry->status === QueueEntryStatus::WAITING) {
                return $entry->refresh();
            }

            if ($entry !== null && $entry->status === QueueEntryStatus::ENABLED) {
                if ($entry->enabled_until !== null && $entry->enabled_until->isFuture()) {
                    return $entry->refresh();
                }

                $entry->update([
                    'status' => QueueEntryStatus::EXPIRED->value,
                    'enabled_at' => null,
                    'enabled_until' => null,
                ]);
                $entry = null;
            }

            $config = $this->getQueueConfig($event);
            $enabledUntil = $this->resolveEnabledUntil($user->id, $event, $config);
            $enabledCount = QueueEntry::query()
                ->where('event_id', $event->id)
                ->where('status', QueueEntryStatus::ENABLED->value)
                ->lockForUpdate()
                ->count();

            $entryData = [
                'status' => QueueEntryStatus::WAITING->value,
                'entered_at' => now(),
                'enabled_at' => null,
                'enabled_until' => null,
            ];

            if ($enabledCount < $config['max_concurrent']) {
                $entryData = [
                    'status' => QueueEntryStatus::ENABLED->value,
                    'entered_at' => now(),
                    'enabled_at' => now(),
                    'enabled_until' => $enabledUntil,
                ];
            }

            if ($entry !== null) {
                $entry->update($entryData);
                return $entry->fresh();
            }

            return QueueEntry::query()->create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                ...$entryData,
            ]);
        });
    }

    // Restituisce lo stato corrente in coda dell'utente per l'evento
    public function getQueueStatus(User $user, Event $event): array
    {
        $this->expireEnabledEntries($event);

        if (! $event->isQueueEnabled()) {
            return [
                'is_queue_enabled' => false,
                'status' => null,
                'position' => null,
                'estimated_wait_seconds' => null,
                'entered_at' => null,
                'enabled_at' => null,
                'enabled_until' => null,
            ];
        }

        $this->promoteWaitingEntries($event);

        $entry = QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', '!=', QueueEntryStatus::COMPLETED->value)
            ->latest('id')
            ->first();

        if ($entry === null) {
            return $this->toQueueStatusPayload($event, null);
        }

        if ($entry->status === QueueEntryStatus::ENABLED && $entry->enabled_until !== null && $entry->enabled_until->isPast()) {
            $entry->update([
                'status' => QueueEntryStatus::EXPIRED->value,
                'enabled_at' => null,
                'enabled_until' => null,
            ]);

            return $this->toQueueStatusPayload($event, null);
        }

        $position = null;
        $estimatedWaitSeconds = null;
        if ($entry->status === QueueEntryStatus::WAITING) {
            $position = QueueEntry::query()
                ->where('event_id', $event->id)
                ->where('status', QueueEntryStatus::WAITING->value)
                ->where('entered_at', '<=', $entry->entered_at)
                ->count();

            $estimatedWaitSeconds = $this->estimateWaitingWaitSeconds($event, $entry, (int) $position);
        }

        return $this->toQueueStatusPayload($event, $entry, $position, $estimatedWaitSeconds);
    }

    // Blocca o consente la presa in carrello verificando stato coda utente
    public function assertUserCanHold(User $user, ?Event $event): void
    {
        if ($event === null) {
            return;
        }

        $status = $this->getQueueStatus($user, $event);

        if (! ($status['is_queue_enabled'] ?? false)) {
            return;
        }

        if ($status['status'] !== QueueEntryStatus::ENABLED->value) {
            throw ValidationException::withMessages([
                'queue' => 'Devi essere stato abilitato dalla coda per acquistare questo evento.',
            ]);
        }
    }

    // Verifica che l'utente possa acquistare tutti gli eventi presenti negli hold
    public function assertCheckoutAllowed(User $user, Collection $holds): void
    {
        $eventIds = $holds
            ->map(static fn (Hold $hold): ?int => $hold->ticket?->ticketType?->event_id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if ($eventIds === []) {
            return;
        }

        $events = Event::query()->whereIn('id', $eventIds)->where('queue_enabled', true)->get();
        foreach ($events as $event) {
            $this->assertUserCanHold($user, $event);
        }
    }

    // Rinnova la finestra abilitato in coda se l'utente è attivo per quell'evento
    public function refreshEnabledWindowForUserAndEvent(User $user, Event $event): void
    {
        if (! $event->isQueueEnabled()) {
            return;
        }

        $entry = QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->latest('id')
            ->first();

        if ($entry === null) {
            return;
        }

        $config = $this->getQueueConfig($event);
        $entry->update([
            'enabled_until' => $this->resolveEnabledUntil($user->id, $event, $config),
        ]);
    }

    // Marca come completate le entry ENABLED dell'utente per gli eventi indicati
    public function markEntriesCompleted(User $user, array $eventIds): void
    {
        if ($eventIds === []) {
            return;
        }

        QueueEntry::query()
            ->where('user_id', $user->id)
            ->whereIn('event_id', $eventIds)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->update([
                'status' => QueueEntryStatus::COMPLETED->value,
                'enabled_at' => null,
                'enabled_until' => null,
            ]);
    }

    // Elabora tutte le code eventi attivi e promuove/scade entry
    public function processQueue(): int
    {
        $updated = 0;

        $eventIds = Event::query()
            ->where('queue_enabled', true)
            ->pluck('id');

        foreach ($eventIds as $eventId) {
            $event = Event::query()->find($eventId);
            if ($event === null) {
                continue;
            }

            $updated += $this->processQueueForEvent($event);
        }

        return $updated;
    }

    // Aggiorna un singolo evento elaborando scadenze e promozioni in coda
    private function processQueueForEvent(Event $event): int
    {
        return DB::transaction(function () use ($event): int {
            $expiredCount = $this->expireEnabledEntries($event);
            $promotedCount = $this->promoteWaitingEntries($event);

            return $expiredCount + $promotedCount;
        });
    }

    // Scade automaticamente le entry ENABLED oltre ora limite
    private function expireEnabledEntries(Event $event): int
    {
        return QueueEntry::query()
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->whereNotNull('enabled_until')
            ->where('enabled_until', '<=', now())
            ->update([
                'status' => QueueEntryStatus::EXPIRED->value,
                'enabled_at' => null,
                'enabled_until' => null,
            ]);
    }

    // Promuove gli utenti in attesa fino a riempire gli slot disponibili
    private function promoteWaitingEntries(Event $event): int
    {
        $config = $this->getQueueConfig($event);
        $enabledCount = QueueEntry::query()
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->count();

        $availableSlots = max(0, $config['max_concurrent'] - $enabledCount);
        if ($availableSlots === 0) {
            return 0;
        }

        $toEnable = QueueEntry::query()
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::WAITING->value)
            ->lockForUpdate()
            ->orderBy('entered_at')
            ->limit($availableSlots)
            ->get();

        if ($toEnable->isEmpty()) {
            return 0;
        }

        foreach ($toEnable as $entry) {
            $enabledUntil = $this->resolveEnabledUntil((int) $entry->user_id, $event, $config);

            $entry->update([
                'status' => QueueEntryStatus::ENABLED->value,
                'enabled_at' => now(),
                'enabled_until' => $enabledUntil,
            ]);
        }

        return $toEnable->count();
    }

    // Stima i secondi di attesa in base alla posizione in coda e durata slot
    private function estimateWaitingWaitSeconds(Event $event, QueueEntry $entry, int $position): int
    {
        if ($position <= 0) {
            return 0;
        }

        $config = $this->getQueueConfig($event);
        $slotAvailability = [];

        $enabledEntries = QueueEntry::query()
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->orderBy('id')
            ->get(['enabled_until']);

        foreach ($enabledEntries as $enabledEntry) {
            $slotAvailability[] = $enabledEntry->enabled_until ?? now();
        }

        while (count($slotAvailability) < $config['max_concurrent']) {
            $slotAvailability[] = now();
        }

        $slotAvailability = $this->sortSlotAvailability($slotAvailability);

        $waitingEntries = QueueEntry::query()
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::WAITING->value)
            ->orderBy('entered_at')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        if ($waitingEntries->isEmpty()) {
            return 0;
        }

        $targetIndex = min($position, $waitingEntries->count()) - 1;
        $waitingEntries = $waitingEntries->values();

        for ($cursor = 0; $cursor <= $targetIndex; $cursor++) {
            $slotReadyAt = array_shift($slotAvailability);
            if ($slotReadyAt === null) {
                $slotReadyAt = now();
            }

            $waitingEntry = $waitingEntries->get($cursor);
            if ($waitingEntry === null) {
                break;
            }

            $slotAvailability[] = $this->resolveEnabledUntil(
                (int) $waitingEntry->user_id,
                $event,
                $config,
                $slotReadyAt,
            );

            $slotAvailability = $this->sortSlotAvailability($slotAvailability);

            if ($waitingEntry->id === $entry->id) {
                return max(0, (int) now()->diffInSeconds($slotReadyAt));
            }
        }

        return 0;
    }

    // Ordina cronologicamente gli istanti di disponibilità degli slot
    private function sortSlotAvailability(array $slotAvailability): array
    {
        usort(
            $slotAvailability,
            static fn (CarbonInterface $first, CarbonInterface $second): int => $first->greaterThan($second) ? 1 : ($first->lessThan($second) ? -1 : 0),
        );

        return $slotAvailability;
    }

    // Calcola la nuova scadenza enabled_until rispettando eventuale hold attivo
    private function resolveEnabledUntil(int $userId, Event $event, array $config, ?CarbonInterface $asOf = null): CarbonInterface
    {
        $now = $asOf ?? now();

        $latestHold = Hold::query()
            ->where('user_id', $userId)
            ->where('status', HoldStatusEnum::ACTIVE->value)
            ->where('expires_at', '>', $now)
            ->whereHas('ticket.ticketType', fn($query) => $query->where('event_id', $event->id))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($latestHold === null || $latestHold->expires_at === null) {
            return $now->addMinutes($config['duration_minutes']);
        }

        return $latestHold->expires_at;
    }

    // Normalizza lo stato coda in formato payload per frontend
    private function toQueueStatusPayload(Event $event, ?QueueEntry $entry = null, ?int $position = null, ?int $estimatedWaitSeconds = null): array
    {
        $config = $this->getQueueConfig($event);
        $estimatedWait = null;

        if ($entry !== null && $entry->status === QueueEntryStatus::WAITING && $position !== null) {
            $estimatedWait = $estimatedWaitSeconds ??
                intdiv((int) $position + $config['max_concurrent'] - 1, $config['max_concurrent']) * ($config['duration_minutes'] * 60);
        }

        return [
            'is_queue_enabled' => true,
            'status' => $entry?->status?->value,
            'position' => $position,
            'estimated_wait_seconds' => $estimatedWait,
            'entered_at' => $entry?->entered_at?->toIso8601String(),
            'enabled_at' => $entry?->enabled_at?->toIso8601String(),
            'enabled_until' => $entry?->enabled_until?->toIso8601String(),
        ];
    }
}
