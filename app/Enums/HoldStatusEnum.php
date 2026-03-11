<?php

namespace App\Enums;

enum HoldStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Attivo',
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
