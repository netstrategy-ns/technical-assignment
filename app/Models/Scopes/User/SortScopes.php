<?php

namespace App\Models\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    public function scopeSortByPublicId(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('public_id', $direction);
    }

    public function scopeSortByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    public function scopeSortByEmail(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('email', $direction);
    }

    public function scopeSortByIsAdmin(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('is_admin', $direction);
    }

    public function scopeSortByCreatedAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('created_at', $direction);
    }

    public function scopeSortByDeletedAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('deleted_at', $direction);
    }
}
