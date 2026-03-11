<?php

namespace App\Models\Scopes\Order;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra ordini per nome utente
    public function scopeFilterByUserName(Builder $query, string $name): Builder
    {
        return $query->whereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', '%' . $name . '%')->orWhere('email', 'like', '%' . $name . '%'));
    }

    // Filtra ordini per codice pubblico
    public function scopeFilterByPublicId(Builder $query, string $publicId): Builder
    {
        return $query->where('public_id', 'like', '%' . $publicId . '%');
    }

    // Filtra ordini per stato
    public function scopeFilterByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Filtra ordini per importo
    public function scopeFilterByTotalAmount(Builder $query, float|int|string $totalAmount): Builder
    {
        return $query->where('total_amount', (float) $totalAmount);
    }

    // Filtra ordini per data di creazione
    public function scopeFilterByCreatedAt(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }
}
