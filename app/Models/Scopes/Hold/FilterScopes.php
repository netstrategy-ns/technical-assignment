<?php

namespace App\Models\Scopes\Hold;

use App\Enums\HoldStatusEnum;
use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra gli hold attivi
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', HoldStatusEnum::ACTIVE->value);
    }

    // Filtra hold con scadenza futura
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }
}
