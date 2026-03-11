<?php

namespace App\Models\Scopes\EventCategory;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra categorie per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra categorie per nome
    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    // Filtra categorie per slug
    public function scopeFilterBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', 'like', '%' . $slug . '%');
    }
}
