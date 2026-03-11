<?php

namespace App\Models\Scopes\VenueType;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina tipologie luogo per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina tipologie luogo per nome
    public function scopeSortByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    // Ordina tipologie luogo per slug
    public function scopeSortBySlug(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('slug', $direction);
    }
}
