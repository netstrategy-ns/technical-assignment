<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoldOverbookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_hold_does_not_allow_overbooking(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $event = Event::factory()->create([
            'sales_start_at' => now()->subHour(),
            'queue_enabled' => false,
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'total_quantity' => 5,
            'max_per_user' => null,
        ]);

        $this->actingAs($userA)
            ->postJson('/holds', [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 5,
            ])
            ->assertStatus(201);

        $this->actingAs($userB)
            ->postJson('/holds', [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('holds', 1);
        $this->assertDatabaseHas('holds', [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'active',
        ]);
    }
}
