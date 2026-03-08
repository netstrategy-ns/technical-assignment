<?php

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hold;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\User;
use App\Models\VenueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createTicketForHoldTest(array $eventOverrides = [], array $ticketOverrides = []): array
{
    $category = EventCategory::create([
        'name' => 'Concerti',
        'slug' => 'concerti',
    ]);

    $venueType = VenueType::create([
        'name' => 'Arena',
        'slug' => 'arena',
    ]);

    $event = Event::create(array_merge([
        'title' => 'Evento Hold Test',
        'description' => 'Descrizione evento hold test',
        'event_category_id' => $category->id,
        'venue_type_id' => $venueType->id,
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(2),
        'sale_starts_at' => now()->subDay(),
        'location' => 'Roma',
        'image_url' => null,
        'is_featured' => false,
        'is_active' => true,
        'available_tickets' => 10,
    ], $eventOverrides));

    $ticketType = TicketType::create([
        'event_id' => $event->id,
        'venue_type_id' => $venueType->id,
        'name' => 'Standard',
    ]);

    TicketTypeQuota::create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 10,
    ]);

    $ticket = Ticket::create(array_merge([
        'ticket_type_id' => $ticketType->id,
        'price' => '49.90',
        'quantity_total' => 10,
        'max_per_user' => 4,
    ], $ticketOverrides));

    return compact('event', 'ticketType', 'ticket');
}

describe('Hold cart actions', function (): void {
    test('POST /cart/hold crea un hold di 10 minuti per un utente autenticato', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForHoldTest();

        actingAs($user);

        $response = post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();

        $hold = Hold::query()->first();

        expect($hold)->not->toBeNull();
        expect($hold->user_id)->toBe($user->id);
        expect($hold->ticket_id)->toBe($ticket->id);
        expect($hold->quantity)->toBe(2);
        expect($hold->status)->toBe(HoldStatusEnum::ACTIVE);
        expect($hold->expires_at->between(now()->addMinutes(9), now()->addMinutes(10)->addSeconds(5)))->toBeTrue();
        expect($ticket->fresh()->getAvailableQuantity())->toBe(8);
    });

    test('POST /cart/hold senza autenticazione reindirizza al login', function (): void {
        ['ticket' => $ticket] = createTicketForHoldTest();

        $response = post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/login');
    });

    test('POST /cart/hold con quantita superiore al disponibile restituisce errore', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createTicketForHoldTest([], [
            'quantity_total' => 2,
        ]);

        actingAs($user);

        $response = from('/events/'.$event->slug)->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 3,
        ]);

        $response
            ->assertRedirect('/events/'.$event->slug)
            ->assertSessionHasErrors('quantity');
    });

    test('POST /cart/hold per evento con vendita non iniziata restituisce errore', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createTicketForHoldTest([
            'sale_starts_at' => now()->addHour(),
        ]);

        actingAs($user);

        $response = from('/events/'.$event->slug)->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ]);

        $response
            ->assertRedirect('/events/'.$event->slug)
            ->assertSessionHasErrors('ticket_id');
    });

    test('lo stesso utente aggiorna il medesimo hold senza duplicarlo', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForHoldTest();

        actingAs($user);

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ])->assertRedirect();

        $firstExpiresAt = Hold::query()->firstOrFail()->expires_at;
        Date::setTestNow(now()->addMinutes(2));

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect();

        $hold = Hold::query()->firstOrFail();

        expect(Hold::query()->count())->toBe(1);
        expect($hold->quantity)->toBe(3);
        expect($hold->expires_at->gt($firstExpiresAt))->toBeTrue();

        Date::setTestNow();
    });
});

describe('Controllo disponibilità e carrello', function (): void {
    test('GET /cart restituisce gli hold attivi dell utente autenticato', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createTicketForHoldTest();

        actingAs($user);

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ])->assertRedirect();

        $response = get('/cart');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/cart/Index')
            ->has('cart.items', 1)
            ->where('cart.items.0.ticket.id', $ticket->id)
            ->where('cart.items.0.event.slug', $event->slug)
            ->where('cart.items.0.quantity', 2)
        );
    });

    test('DELETE /cart/hold quando scade hold e ripristina la disponibilita', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForHoldTest();

        actingAs($user);

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ])->assertRedirect();

        $hold = Hold::query()->firstOrFail();

        delete('/cart/hold/'.$hold->id)->assertRedirect();

        $hold->refresh();

        expect($hold->status)->toBe(HoldStatusEnum::EXPIRED);
        expect($ticket->fresh()->getAvailableQuantity())->toBe(10);
    });

    test('la disponibilita per ticket considera venduti e hold validi', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createTicketForHoldTest();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => '149.70',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'ticket_id' => $ticket->id,
            'quantity' => 3,
            'unit_price' => $ticket->price,
        ]);

        actingAs($otherUser);

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ])->assertRedirect();

        expect($ticket->fresh()->getAvailableQuantity())->toBe(5);

        $response = get('/events/'.$event->slug);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('event.ticket_types.0.available_quantity', 5)
            ->where('event.ticket_types.0.tickets.0.available_quantity', 5)
        );
    });
});
