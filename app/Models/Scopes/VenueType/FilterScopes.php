<?php

namespace App\Models\Scopes\VenueType;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra tipologie luogo per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra tipologie luogo per nome
    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    // Filtra tipologie luogo per slug
    public function scopeFilterBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', 'like', '%' . $slug . '%');
    }
}
