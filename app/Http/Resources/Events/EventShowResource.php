<?php

namespace App\Http\Resources\Events;

use App\Http\Resources\Tickets\TicketTypeShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventShowResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'image_url' => $this->image_url,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'sale_starts_at' => $this->sale_starts_at?->toIso8601String(),
            'category' => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null,
            'venueType' => $this->venueType ? ['id' => $this->venueType->id, 'name' => $this->venueType->name] : null,
            'ticket_types' => TicketTypeShowResource::collection($this->whenLoaded('ticketTypes')),
        ];
    }
}
