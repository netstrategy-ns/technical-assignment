<?php

namespace App\Models\Scopes\OrderItem;

use Illuminate\Database\Eloquent\Builder;

trait FilterScopes
{
    // Filtra gli order item per codice ordine
    public function scopeFilterByOrderPublicId(Builder $query, string $publicId): Builder
    {
        return $query->whereHas('order', fn (Builder $relatedQuery): Builder => $relatedQuery->where('public_id', 'like', '%' . $publicId . '%'));
    }

    // Filtra gli order item per nome tipologia
    public function scopeFilterByTicketTypeName(Builder $query, string $ticketTypeName): Builder
    {
        return $query->whereHas(
            'ticket.ticketType',
            fn (Builder $relatedQuery): Builder => $relatedQuery->where('name', 'like', '%' . $ticketTypeName . '%'),
        );
    }

    // Filtra gli order item per quantità
    public function scopeFilterByQuantity(Builder $query, int $quantity): Builder
    {
        return $query->where('quantity', $quantity);
    }

    // Filtra gli order item per prezzo unitario
    public function scopeFilterByUnitPrice(Builder $query, float|int|string $unitPrice): Builder
    {
        return $query->where('unit_price', (float) $unitPrice);
    }
}
