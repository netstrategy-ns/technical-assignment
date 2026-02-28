<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutCreatesOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_and_tickets_and_consumes_holds(): void
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

        $this->actingAs($user)
            ->postJson('/checkout', [
                'event_id' => $event->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('tickets', 2);

        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals($event->id, $order->event_id);
        $this->assertEquals(100, (float) $order->total_amount);

        $this->assertDatabaseHas('holds', [
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'status' => 'consumed',
        ]);
    }
}
