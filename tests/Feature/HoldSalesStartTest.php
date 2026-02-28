<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoldSalesStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_hold_blocked_before_sales_start(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'sales_start_at' => now()->addHour(),
            'queue_enabled' => false,
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'total_quantity' => 10,
            'max_per_user' => null,
        ]);

        $this->actingAs($user)
            ->postJson('/holds', [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ])
            ->assertStatus(422);
    }
}
