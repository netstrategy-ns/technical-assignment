<?php

namespace App\Enums;

enum QueueEntryStatus: string
{
    case WAITING = 'waiting';
    case ENABLED = 'enabled';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';
}
