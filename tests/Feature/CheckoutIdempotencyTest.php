<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_is_idempotent_by_key(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'sales_start_at' => now()->subHour(),
            'queue_enabled' => false,
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'total_quantity' => 10,
            'max_per_user' => null,
            'price' => 50,
        ]);

        $this->actingAs($user)
            ->postJson('/holds', [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
            ])
            ->assertStatus(201);

        $key = 'test-idempotency-key';

        $this->actingAs($user)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/checkout', [
                'event_id' => $event->id,
            ])
            ->assertStatus(201);

        $this->actingAs($user)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/checkout', [
                'event_id' => $event->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount('orders', 1);

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertEquals($key, $order->idempotency_key);
    }
}
