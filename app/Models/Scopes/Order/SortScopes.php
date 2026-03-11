<?php

namespace App\Models\Scopes\Order;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina ordini per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }

    // Ordina ordini per nome utente
    public function scopeSortByUserName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('users as u', 'u.id', '=', 'orders.user_id')
            ->orderBy('u.name', $direction)
            ->select('orders.*');
    }

    // Ordina ordini per codice pubblico
    public function scopeSortByPublicId(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('public_id', $direction);
    }

    // Ordina ordini per stato
    public function scopeSortByStatus(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('status', $direction);
    }

    // Ordina ordini per importo
    public function scopeSortByTotalAmount(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('total_amount', $direction);
    }

    // Ordina ordini per data di creazione
    public function scopeSortByCreatedAt(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('created_at', $direction);
    }
}
