<?php

namespace App\Models\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina utenti per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina utenti per identificativo pubblico
    public function scopeSortByPublicId(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('public_id', $direction);
    }

    // Ordina utenti per nome
    public function scopeSortByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }

    // Ordina utenti per email
    public function scopeSortByEmail(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('email', $direction);
    }

    // Ordina utenti per flag admin
    public function scopeSortByIsAdmin(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('is_admin', $direction);
    }

    // Ordina utenti per data creazione
    public function scopeSortByCreatedAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('created_at', $direction);
    }

    // Ordina utenti per data cancellazione
    public function scopeSortByDeletedAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('deleted_at', $direction);
    }
}
