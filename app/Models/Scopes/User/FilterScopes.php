<?php

namespace App\Models\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra utenti per id
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    // Filtra utenti per identificativo pubblico
    public function scopeFilterByPublicId(Builder $query, string $publicId): Builder
    {
        return $query->where('public_id', 'like', '%' . $publicId . '%');
    }

    // Filtra utenti per nome
    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    // Filtra utenti per email
    public function scopeFilterByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    // Filtra utenti per flag admin
    public function scopeFilterByIsAdmin(Builder $query, bool $isAdmin): Builder
    {
        return $query->where('is_admin', $isAdmin);
    }

    // Filtra utenti per data creazione
    public function scopeFilterByCreatedAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    // Filtra utenti per data cancellazione
    public function scopeFilterByDeletedAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('deleted_at', $date);
    }
}
