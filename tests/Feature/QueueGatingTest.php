<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQueueEntry;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_blocks_hold_until_allowed(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'sales_start_at' => now()->subHour(),
            'queue_enabled' => true,
            'queue_max_concurrent' => 1,
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

        $this->actingAs($user)
            ->postJson(route('queue.enter'), [
                'event_id' => $event->id,
            ])
            ->assertStatus(201);

        EventQueueEntry::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'allowed',
                'allowed_until' => now()->addMinutes(5),
            ]);

        $this->actingAs($user)
            ->postJson('/holds', [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ])
            ->assertStatus(201);
    }
}
