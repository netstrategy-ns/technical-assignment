<?php

namespace App\Enums;

enum QueueEntryStatus: string
{
    case WAITING = 'waiting';
    case ENABLED = 'enabled';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'In coda',
            self::ENABLED => 'Abilitato',
            self::EXPIRED => 'Scaduto',
            self::COMPLETED => 'Completato',
        };
    }

    public static function selectOptions(): array
    {
        return array_map(static fn (self $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
        ], self::cases());
    }
}
