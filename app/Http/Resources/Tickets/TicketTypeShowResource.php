<?php

namespace App\Http\Resources\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTypeShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quota_quantity' => (int) ($this->quota?->quantity ?? 0),
            'available_quantity' => $this->getAvailableQuantity(),
            'tickets' => $this->tickets->map(static fn ($ticket) => [
                'id' => $ticket->id,
                'price' => $ticket->price,
                'quantity_total' => $ticket->quantity_total,
                'max_per_user' => $ticket->max_per_user,
                'available_quantity' => $ticket->getAvailableQuantity(),
                'user_hold_quantity' => $request->user() === null
                    ? 0
                    : (int) $ticket->holds()
                        ->active()
                        ->valid()
                        ->where('user_id', $request->user()->id)
                        ->sum('quantity'),
            ]),
        ];
    }
}
