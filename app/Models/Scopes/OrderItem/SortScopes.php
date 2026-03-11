<?php

namespace App\Models\Scopes\OrderItem;

use Illuminate\Database\Eloquent\Builder;

trait SortScopes
{
    // Ordina gli order item per codice ordine
    public function scopeSortByOrderPublicId(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('orders as o', 'o.id', '=', 'order_items.order_id')
            ->orderBy('o.public_id', $direction)
            ->select('order_items.*');
    }

    // Ordina gli order item per nome tipologia
    public function scopeSortByTicketTypeName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query
            ->leftJoin('tickets as t', 't.id', '=', 'order_items.ticket_id')
            ->leftJoin('ticket_types as tt', 'tt.id', '=', 't.ticket_type_id')
            ->orderBy('tt.name', $direction)
            ->select('order_items.*');
    }

    // Ordina gli order item per quantità
    public function scopeSortByQuantity(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('quantity', $direction);
    }

    // Ordina gli order item per prezzo unitario
    public function scopeSortByUnitPrice(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('unit_price', $direction);
    }

    // Ordina gli order item per id
    public function scopeSortById(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('id', $direction);
    }
}
