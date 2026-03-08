<?php

namespace App\Enums;

enum HoldStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';
}
