<?php

namespace App\Models\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    public function scopeFilterById(Builder $query, int $id): Builder
    {
        return $query->where('id', $id);
    }

    public function scopeFilterByPublicId(Builder $query, string $publicId): Builder
    {
        return $query->where('public_id', 'like', '%' . $publicId . '%');
    }

    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeFilterByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    public function scopeFilterByIsAdmin(Builder $query, bool $isAdmin): Builder
    {
        return $query->where('is_admin', $isAdmin);
    }

    public function scopeFilterByCreatedAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeFilterByDeletedAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('deleted_at', $date);
    }
}
