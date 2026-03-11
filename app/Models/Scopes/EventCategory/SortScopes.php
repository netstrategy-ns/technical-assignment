<?php

namespace App\Models\Scopes\EventCategory;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina categorie per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina categorie per nome
    public function scopeSortByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    // Ordina categorie per slug
    public function scopeSortBySlug(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('slug', $direction);
    }
}
